document.addEventListener('DOMContentLoaded', function() {
    // Get container elements
    const upcomingContainer = document.getElementById('upcoming-sessions');
    const historyContainer = document.getElementById('history-sessions');
    const reportContainer = document.getElementById('report-sessions');
    
    // Handle tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the tab type
            const tabType = this.getAttribute('data-tab');
            
            // Handle tab content switching logic
            if (tabType === 'upcoming') {
                console.log('Showing upcoming sessions');
                upcomingContainer.classList.remove('hidden');
                historyContainer.classList.add('hidden');
                if (reportContainer) reportContainer.classList.add('hidden');
            } else if (tabType === 'history') {
                console.log('Showing history sessions');
                upcomingContainer.classList.add('hidden');
                historyContainer.classList.remove('hidden');
                if (reportContainer) reportContainer.classList.add('hidden');
            } else if (tabType === 'reports') {
                console.log('Showing reports and refunds');
                upcomingContainer.classList.add('hidden');
                historyContainer.classList.add('hidden');
                if (reportContainer) reportContainer.classList.remove('hidden');
            }
        });
    });

    
});
