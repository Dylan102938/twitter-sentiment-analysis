    var ctx = document.getElementById('sentChart').getContext('2d');
    var labelArr = new Array();
    var data = new Array();

    for (var i = 0; i < 8; i++) {
        labelArr.push(document.getElementsByClassName("labels")[i].value);
    }

    for (i = 0; i < 8; i++) {
        data.push(document.getElementsByClassName("data")[i].value);
    }

    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelArr,
            datasets: [{
                backgroundColor: 'rgba(255, 255, 255, 0.4)',
                borderColor: 'white',
                borderWidth: 10,
                borderCapStyle: "round",
                pointBorderWidth: 0,
                pointRadius: 15,
                pointHoverRadius: 15,
                pointBackgroundColor: 'rgba(255,255,255,0.5)',
                pointHoverBackgroundColor: 'rgba(255,255,255,0.5)',
                pointBorderColor: 'rgba(255,255,255,0)',
                data: data
            }]
        },
        options: {
            legend: {
                display: false
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 20,
                    bottom: 20
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        fontColor: "transparent",
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false,
                    }
                }],
                xAxes: [{
                    ticks: {
                        fontColor: "transparent"
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false,
                    }
                }]
            }
        }
    });
