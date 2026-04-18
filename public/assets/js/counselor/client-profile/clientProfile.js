
const ctxLine = document.getElementById('lineChart');
const ctxBar = document.getElementById('barChart');
const dataLine = {
    labels: ['Jul1', 'Jul8', 'Jul15', 'Jul22', 'Jul29'],
    datasets: [{
        label: 'Mood',
        data: [65, 59, 80, 81, 56],
        fill: false,
        borderColor: '#4F7F96',
        tension: 0.1
    }]
};


new Chart(ctxLine,{
    type: 'line',
    data: dataLine,
    options: {
    responsive: true,
        scales: { y: { beginAtZero: true } }
}
});


const dataBar = {
    labels: ['Jul1', 'Jul8', 'Jul15', 'Jul22', 'Jul29'],
    datasets: [{
        label: 'Mood',
        data: [65, 59, 80, 81, 56],
        backgroundColor: '#DFF0F778',     // color of bars
        borderColor: '#3C6577',         // outline color
        borderWidth: 1,                 // outline width
        borderRadius: 4,                // slightly rounded corners
        barPercentage: 0.7,             // width of each bar (0–1)
        categoryPercentage: 0.8         // spacing between groups
    }]
};



new Chart(ctxBar, {
    type: 'bar',
    data: dataBar,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                labels: {
                    color: '#333'
                }
            }
        }
    }
});
