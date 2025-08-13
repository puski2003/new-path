(function (global) {
    function noop() {}

    function createTask(options) {
        var settings = options || {};
        var state = {
            timer: null,
            running: false,
            interval: Number(settings.interval) || 0,
        };

        function run() {
            if (state.running || typeof settings.request !== 'function') {
                return Promise.resolve();
            }

            if (typeof settings.shouldRun === 'function' && settings.shouldRun(state) === false) {
                return Promise.resolve();
            }

            state.running = true;

            return Promise.resolve()
                .then(function () {
                    return settings.request(state);
                })
                .then(function (result) {
                    (settings.onSuccess || noop)(result, state);
                    return result;
                })
                .catch(function (error) {
                    (settings.onError || noop)(error, state);
                })
                .finally(function () {
                    state.running = false;
                });
        }

        function stop() {
            if (state.timer) {
                clearInterval(state.timer);
                state.timer = null;
            }
            (settings.onStop || noop)(state);
        }

        function start() {
            stop();

            if (settings.runImmediately !== false) {
                run();
            }

            if (state.interval > 0) {
                state.timer = setInterval(run, state.interval);
            }
        }

        return {
            start: start,
            stop: stop,
            runNow: run,
            getState: function () {
                return state;
            },
        };
    }

    global.NewPathPolling = {
        createTask: createTask,
    };
}(window));
