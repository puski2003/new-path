document.addEventListener('DOMContentLoaded', function() {
    // Calendar functionality
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');

    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', function() {
            console.log('Previous month clicked');
        });

        nextBtn.addEventListener('click', function() {
            console.log('Next month clicked');
        });
    }

    // Time slot selection
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            timeSlots.forEach(s => s.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Calendar day selection
    const calendarDays = document.querySelectorAll('.calendar-days span');
    calendarDays.forEach(day => {
        if (day.textContent.trim() !== '') {
            day.addEventListener('click', function() {
                calendarDays.forEach(d => d.classList.remove('available'));
                this.classList.add('available');
            });
        }
    });

    // Book now button
    const bookBtn = document.querySelector('.book-now-btn');
    if (bookBtn) {
        bookBtn.addEventListener('click', function() {
            const selectedDate = document.querySelector('.calendar-days span.available');
            const selectedTime = document.querySelector('.time-slot.selected');

            if (selectedDate && selectedTime) {
                console.log(`Booking appointment for ${selectedDate.textContent} at ${selectedTime.textContent}`);
            } else {
                console.log('Please select both a date and time slot');
            }
        });
    }
});
