<?php
header('Content-Type: application/json');

function cleanPlanText($value): string
{
    if (is_array($value)) {
        $value = implode(', ', array_map('strval', $value));
    }

    $value = trim((string) $value);
    $value = preg_replace('/^\s*[-*•]\s*/u', '', $value);
    $value = preg_replace('/\s+/', ' ', $value);

    return trim((string) $value);
}

function splitReadableLines($value): array
{
    if (is_array($value)) {
        $lines = [];
        foreach ($value as $item) {
            $clean = cleanPlanText($item);
            if ($clean !== '') {
                $lines[] = $clean;
            }
        }
        return $lines;
    }

    $value = str_replace(["\r\n", "\r"], "\n", (string) $value);
    $parts = preg_split('/\n+/', $value) ?: [];
    $lines = [];

    foreach ($parts as $part) {
        $clean = cleanPlanText($part);
        if ($clean !== '') {
            $lines[] = $clean;
        }
    }

    return $lines;
}

function extractJsonObject(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    $start = strpos($text, '{');
    $end = strrpos($text, '}');

    if ($start === false || $end === false || $end <= $start) {
        return '';
    }

    return substr($text, $start, $end - $start + 1);
}

/**
 * Escape raw control characters inside JSON string values.
 * LLMs often emit raw newlines inside strings, which makes json_decode fail.
 */
function sanitizeJson(string $json): string
{
    $out     = '';
    $inStr   = false;
    $escaped = false;
    $len     = strlen($json);

    for ($i = 0; $i < $len; $i++) {
        $ch = $json[$i];

        if ($escaped) {
            $out    .= $ch;
            $escaped = false;
            continue;
        }

        if ($ch === '\\' && $inStr) {
            $out    .= $ch;
            $escaped = true;
            continue;
        }

        if ($ch === '"') {
            $inStr = !$inStr;
            $out  .= $ch;
            continue;
        }

        if ($inStr) {
            if ($ch === "\n") { $out .= '\\n';  continue; }
            if ($ch === "\r") { $out .= '\\r';  continue; }
            if ($ch === "\t") { $out .= '\\t';  continue; }
        }

        $out .= $ch;
    }

    return $out;
}

function decodePlanPayload(string $text): ?array
{
    $trimmed = trim($text);
    $candidates = array_values(array_filter([
        $trimmed,
        extractJsonObject($trimmed),
    ]));

    foreach ($candidates as $candidate) {
        // 1. Standard decode
        $decoded = json_decode($candidate, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 2. Sanitize raw control chars inside strings (common LLM issue), retry
        $sanitized = sanitizeJson($candidate);
        $decoded   = json_decode($sanitized, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // 3. Trailing-comma fix on sanitized candidate
        $relaxed = preg_replace("/,\s*([}\]])/", '$1', $sanitized);
        if (!is_string($relaxed)) {
            continue;
        }

        $decoded = json_decode($relaxed, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
    }

    return null;
}

function extractLabelValue(string $text, array $labels): string
{
    foreach ($labels as $label) {
        $pattern = '/(?:^|\n)\s*' . preg_quote($label, '/') . '\s*:\s*(.+?)(?=\n\s*[A-Za-z][A-Za-z0-9 \-\/()]*\s*:|\z)/is';
        if (preg_match($pattern, $text, $matches)) {
            return cleanPlanText($matches[1]);
        }
    }

    return '';
}

function parseBullets(string $text): array
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $matches = [];
    preg_match_all('/(?:^|\n)\s*(?:[-*•]|\d+[.)])\s+(.+)/u', $text, $matches);

    if (!empty($matches[1])) {
        return array_values(array_filter(array_map('cleanPlanText', $matches[1])));
    }

    return splitReadableLines($text);
}

function normaliseTask($task): array
{
    $taskTypes = ['custom', 'journal', 'meditation', 'session', 'exercise'];
    $recurrences = ['', 'daily', 'weekly', 'bi-weekly'];

    if (is_string($task)) {
        return [
            'title' => cleanPlanText($task),
            'type' => 'custom',
            'recurrence' => '',
        ];
    }

    if (!is_array($task)) {
        return [
            'title' => '',
            'type' => 'custom',
            'recurrence' => '',
        ];
    }

    $type = strtolower(trim((string) ($task['type'] ?? 'custom')));
    $recurrence = strtolower(trim((string) ($task['recurrence'] ?? '')));

    if (!in_array($type, $taskTypes, true)) {
        $type = 'custom';
    }

    if (!in_array($recurrence, $recurrences, true)) {
        $recurrence = '';
    }

    return [
        'title' => cleanPlanText($task['title'] ?? ''),
        'type' => $type,
        'recurrence' => $recurrence,
    ];
}

function normalisePhase($phase, string $fallbackName): array
{
    $phase = is_array($phase) ? $phase : ['tasks' => $phase];
    $tasks = [];
    $milestones = [];

    foreach ((array) ($phase['tasks'] ?? []) as $task) {
        $normalisedTask = normaliseTask($task);
        if ($normalisedTask['title'] !== '') {
            $tasks[] = $normalisedTask;
        }
    }

    foreach ((array) ($phase['milestones'] ?? []) as $milestone) {
        $cleanMilestone = cleanPlanText($milestone);
        if ($cleanMilestone !== '') {
            $milestones[] = $cleanMilestone;
        }
    }

    if (empty($tasks) && !empty($phase['tasks']) && !is_array($phase['tasks'])) {
        foreach (splitReadableLines($phase['tasks']) as $taskLine) {
            $tasks[] = normaliseTask($taskLine);
        }
    }

    if (empty($milestones) && !empty($phase['milestones']) && !is_array($phase['milestones'])) {
        $milestones = splitReadableLines($phase['milestones']);
    }

    return [
        'name' => cleanPlanText($phase['name'] ?? $fallbackName),
        'tasks' => array_slice($tasks, 0, 5),
        'milestones' => array_slice($milestones, 0, 3),
    ];
}

function parsePhaseBlock(string $block, string $fallbackName): array
{
    $name = extractLabelValue($block, ['Name']) ?: $fallbackName;
    $tasksText = extractLabelValue($block, ['Tasks', 'Action Steps']);
    $milestonesText = extractLabelValue($block, ['Milestones', 'Milestone']);

    $tasks = [];
    foreach (parseBullets($tasksText) as $task) {
        $tasks[] = normaliseTask($task);
    }

    return [
        'name' => $name,
        'tasks' => array_slice($tasks, 0, 5),
        'milestones' => array_slice(parseBullets($milestonesText), 0, 3),
    ];
}

function parsePlanFromReadableText(string $text): ?array
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
    if ($text === '') {
        return null;
    }

    $phaseMatches = [];
    preg_match_all('/(?:^|\n)\s*(?:Phase\s*)?([123])\s*[:.-]?\s*([^\n]*)\n(.*?)(?=(?:\n\s*(?:Phase\s*)?[123]\s*[:.-]?\s*[^\n]*\n)|\z)/is', $text, $phaseMatches, PREG_SET_ORDER);

    $phases = [];
    foreach ($phaseMatches as $match) {
        $phaseNum = (string) $match[1];
        $phaseName = cleanPlanText($match[2] ?: '');
        $phases[$phaseNum] = parsePhaseBlock($match[3], $phaseName !== '' ? $phaseName : match ($phaseNum) {
            '1' => 'Stabilization',
            '2' => 'Reduction',
            default => 'Maintenance',
        });
    }

    $plan = [
        'title' => extractLabelValue($text, ['Title', 'Plan Title']),
        'goal' => extractLabelValue($text, ['Goal', 'Primary Goal', 'Plan Goal']),
        'description' => extractLabelValue($text, ['Description', 'Overview', 'Summary']),
        'startDate' => extractLabelValue($text, ['Start Date']),
        'endDate' => extractLabelValue($text, ['End Date', 'Target Completion Date']),
        'shortTermGoalTitle' => extractLabelValue($text, ['Short-term Goal', 'Short Term Goal']),
        'shortTermGoalDays' => (int) extractLabelValue($text, ['Short-term Goal Days', 'Short Term Goal Days']),
        'longTermGoalTitle' => extractLabelValue($text, ['Long-term Goal', 'Long Term Goal']),
        'longTermGoalDays' => (int) extractLabelValue($text, ['Long-term Goal Days', 'Long Term Goal Days']),
        'notes' => extractLabelValue($text, ['Notes', 'Care Notes', 'Counselor Notes']),
        'phases' => $phases,
    ];

    if ($plan['title'] === '' && $plan['description'] === '' && empty($plan['phases'])) {
        $lines = splitReadableLines($text);
        if (!empty($lines)) {
            $plan['title'] = $lines[0];
            $plan['description'] = implode(' ', array_slice($lines, 1, 3));
        }
    }

    if ($plan['title'] === '' && $plan['description'] === '' && empty($plan['phases'])) {
        return null;
    }

    return $plan;
}

function normaliseDateValue($value, string $fallback): string
{
    $value = trim((string) $value);
    if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    $timestamp = strtotime($value);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return $fallback;
}

function normalisePlan(array $plan): array
{
    $today = date('Y-m-d');
    $defaultEnd = date('Y-m-d', strtotime('+3 months'));
    $phases = is_array($plan['phases'] ?? null) ? $plan['phases'] : [];

    return [
        'title' => cleanPlanText($plan['title'] ?? 'Recovery Support Plan'),
        'goal' => cleanPlanText($plan['goal'] ?? ''),
        'description' => cleanPlanText($plan['description'] ?? ''),
        'startDate' => normaliseDateValue($plan['startDate'] ?? '', $today),
        'endDate' => normaliseDateValue($plan['endDate'] ?? '', $defaultEnd),
        'shortTermGoalTitle' => cleanPlanText($plan['shortTermGoalTitle'] ?? ''),
        'shortTermGoalDays' => max(1, (int) ($plan['shortTermGoalDays'] ?? 30)),
        'longTermGoalTitle' => cleanPlanText($plan['longTermGoalTitle'] ?? ''),
        'longTermGoalDays' => max(1, (int) ($plan['longTermGoalDays'] ?? 90)),
        'phases' => [
            '1' => normalisePhase($phases['1'] ?? $phases[1] ?? [], 'Stabilization'),
            '2' => normalisePhase($phases['2'] ?? $phases[2] ?? [], 'Reduction'),
            '3' => normalisePhase($phases['3'] ?? $phases[3] ?? [], 'Maintenance'),
        ],
        'notes' => cleanPlanText($plan['notes'] ?? ''),
    ];
}

if (!Request::isPost()) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = (string) file_get_contents('php://input');
$parsed = json_decode($body, true);
$prompt = trim((string) ($parsed['prompt'] ?? ''));

if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Prompt is required']);
    exit;
}

$openRouterKey = env('OPENROUTER_API_KEY', '');
$geminiKey     = env('GEMINI_API_KEY', '');

if ($openRouterKey === '' && $geminiKey === '') {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'AI service is not configured. Add OPENROUTER_API_KEY to .env']);
    exit;
}

$systemPrompt = <<<'PROMPT'
You are an expert addiction-recovery counselor assistant. A counselor has described a recovery plan they want to create for a client.
Generate a complete, clinically appropriate recovery plan.

Return ONLY valid JSON with exactly this structure:
{
  "title": "Short plan title",
  "goal": "One-sentence primary goal",
  "description": "2-3 sentence plan description",
  "startDate": "YYYY-MM-DD (today)",
  "endDate": "YYYY-MM-DD (based on duration in prompt, default 3 months)",
  "shortTermGoalTitle": "Short-term goal text",
  "shortTermGoalDays": 30,
  "longTermGoalTitle": "Long-term goal text",
  "longTermGoalDays": 90,
  "phases": {
    "1": {
      "name": "Stabilization",
      "tasks": [
        { "title": "task description", "type": "custom", "recurrence": "" },
        { "title": "task description", "type": "session", "recurrence": "weekly" }
      ],
      "milestones": ["milestone text"]
    },
    "2": {
      "name": "Reduction",
      "tasks": [
        { "title": "task description", "type": "meditation", "recurrence": "daily" },
        { "title": "task description", "type": "exercise", "recurrence": "weekly" }
      ],
      "milestones": ["milestone text"]
    },
    "3": {
      "name": "Maintenance",
      "tasks": [
        { "title": "task description", "type": "custom", "recurrence": "" },
        { "title": "task description", "type": "session", "recurrence": "weekly" }
      ],
      "milestones": ["milestone text"]
    }
  },
  "notes": "Private counselor notes about this plan"
}

Rules:
- task.type must be one of: custom, journal, meditation, session, exercise
- task.recurrence must be one of: "" (one-time), "daily", "weekly", "bi-weekly"
- Provide 3-5 tasks per phase
- Dates must use YYYY-MM-DD format
- Keep all text concise and clinically appropriate
- All string values must be on a single line — no literal newlines inside strings
- No markdown fences
- No explanatory text before or after the JSON

Few-shot example:
Input: "Create a 3-month alcohol recovery plan for a client who needs weekly therapy, daily journaling, and relapse prevention support."
Output:
{
    "title": "Alcohol Recovery Support Plan",
    "goal": "Support sustained sobriety and strengthen relapse-prevention skills.",
    "description": "This plan focuses on stabilization, habit change, and long-term maintenance. It combines therapy, journaling, coping practice, and recovery structure.",
    "startDate": "2026-04-21",
    "endDate": "2026-07-21",
    "shortTermGoalTitle": "Complete the first 30 days sober with consistent support",
    "shortTermGoalDays": 30,
    "longTermGoalTitle": "Maintain sobriety for 90 days and build a relapse-prevention routine",
    "longTermGoalDays": 90,
    "phases": {
        "1": {
            "name": "Stabilization",
            "tasks": [
                {"title": "Attend one weekly therapy session", "type": "session", "recurrence": "weekly"},
                {"title": "Write a daily journal check-in", "type": "journal", "recurrence": "daily"},
                {"title": "Practice a 5-minute grounding exercise", "type": "meditation", "recurrence": "daily"}
            ],
            "milestones": ["Complete the first week sober", "Identify top relapse triggers"]
        },
        "2": {
            "name": "Reduction",
            "tasks": [
                {"title": "Review craving patterns with therapist", "type": "session", "recurrence": "weekly"},
                {"title": "Use a craving log after difficult moments", "type": "journal", "recurrence": "daily"},
                {"title": "Do a 10-minute stress reset routine", "type": "exercise", "recurrence": "daily"}
            ],
            "milestones": ["Reduce high-risk situations", "Use coping skills without prompting"]
        },
        "3": {
            "name": "Maintenance",
            "tasks": [
                {"title": "Continue weekly recovery support", "type": "session", "recurrence": "weekly"},
                {"title": "Track sobriety wins in a journal", "type": "journal", "recurrence": "weekly"},
                {"title": "Practice a relapse-prevention review", "type": "meditation", "recurrence": "weekly"}
            ],
            "milestones": ["Build a stable sober routine", "Prepare a follow-up plan"]
        }
    },
    "notes": "Keep tone supportive, practical, and nonjudgmental."
}
PROMPT;

// ── OpenRouter (primary) ──────────────────────────────────────────────────────
if ($openRouterKey !== '') {
    $requestBody = json_encode([
        'model' => 'meta-llama/llama-3.3-70b-instruct',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => "Counselor request:\n" . $prompt],
        ],
        'temperature' => 0.6,
        'max_tokens'  => 2048,
    ]);

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openRouterKey,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => 'Could not reach AI service. Check your internet connection.']);
        exit;
    }

    if ($httpCode !== 200) {
        $errBody = json_decode($raw, true);
        $errMsg  = $errBody['error']['message'] ?? ('OpenRouter API error (' . $httpCode . ')');
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => $errMsg]);
        exit;
    }

    $response = json_decode($raw, true);
    $planText = $response['choices'][0]['message']['content'] ?? '';

// ── Gemini (fallback) ─────────────────────────────────────────────────────────
} else {
    $requestBody = json_encode([
        'contents' => [[
            'parts' => [[
                'text' => $systemPrompt . "\n\nCounselor request:\n" . $prompt,
            ]],
        ]],
        'generationConfig' => [
            'temperature'      => 0.6,
            'maxOutputTokens'  => 2048,
            'responseMimeType' => 'application/json',
        ],
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($geminiKey);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $requestBody,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$raw) {
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => 'Could not reach AI service. Check your internet connection.']);
        exit;
    }

    if ($httpCode !== 200) {
        $errBody = json_decode($raw, true);
        $errMsg  = $errBody['error']['message'] ?? ('Gemini API error (' . $httpCode . ')');
        http_response_code(502);
        echo json_encode(['ok' => false, 'error' => $errMsg]);
        exit;
    }

    $geminiResp = json_decode($raw, true);
    $planText   = $geminiResp['candidates'][0]['content']['parts'][0]['text'] ?? '';
}

$planText = preg_replace('/^```(?:json)?\s*/m', '', $planText);
$planText = preg_replace('/\s*```$/m', '', $planText);
$planText = trim((string) $planText);

$plan = decodePlanPayload($planText);
if (!is_array($plan)) {
    $plan = parsePlanFromReadableText($planText);
}

if (!is_array($plan)) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'AI returned an unreadable response. Please try again with a clearer description.']);
    exit;
}

$plan = normalisePlan($plan);

echo json_encode(['ok' => true, 'plan' => $plan]);
exit;
