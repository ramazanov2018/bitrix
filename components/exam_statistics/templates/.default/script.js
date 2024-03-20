
"use strict";

Chart.register(ChartDataLabels);

(function ($) {
    $(document).ready(function () {
        $('select[name="stat_districts[]"]').select2({
            placeholder: "Округ проведения экзаменов"
        });
        $('select[name="stat_reg[]"]').select2({
            placeholder: "Регион проведения экзаменов"
        });
        $('select[name="stat_loc[]"]').select2({
            placeholder: "Место проведения экзаменов"
        });
        $('select[name="stat_type[]"]').select2({
            placeholder: "Вариант экзамена"
        });
        $('select[name="stat_month"]').select2({
            placeholder: "Месяц"
        });
        $('select[name="stat_years"]').select2({
            placeholder: "Год"
        }); // Сброс

    });
})(jQuery);

var Diagrams = function (diagramByQuantity, histogram, diagramByScores){
    var diagramByQuantityData = Object.values(diagramByQuantity.quantity);
    var diagramByQuantityLabels = Object.values(diagramByQuantity.names);
    //var diagramByQuantityColors = Object.values(diagramByQuantity.colors);
    //
    var histogramData = Object.values(histogram.quantity);
    var histogramLabels = Object.values(histogram.names);

    var diagramByScoresData = Object.values(diagramByScores.quantity);
    var diagramByScoresLabels = Object.values(diagramByScores.names);
    //var diagramByQuantityColors = Object.values(diagramByQuantity.colors);

    var diagrammBlue = document.getElementById("statisticsDiagrammBlue"),
        diagrammBlueInit = new Chart(diagrammBlue, {
            type: 'bar',
            data: {
                labels: diagramByQuantityLabels,
                datasets: [{
                    label: 'прошло экзамен',
                    data: diagramByQuantityData,
                    backgroundColor: ['#001AFF', '#001AFFCC', '#001AFF99', '#001AFF66', '#001AFF33'],

                }]
            },
         options: {
             layout: {
                 padding: {
                     top: 24
                 }
             },
             plugins: {
                 legend: {
                     display: false
                 },
                 /*tooltip: {
                     enabled: false
                 },*/
                 datalabels: {
                     anchor: 'end',
                     align: 'top',
                     font: {
                         size: '12px'
                     }
                 }
             },
             scales: {
                 y: {
                     grid: {
                         display: false
                     },
                     ticks: {
                         display: false // beginAtZero: true,

                     }
                 },
                 x: {
                     grid: {
                         display: false
                     },
                     ticks: {
                         display: false
                     }
                 }
             }
         }
        }),
        diagrammGreen = document.getElementById("statisticsDiagrammGreen"),
        diagrammGreenInit = new Chart(diagrammGreen, {
         type: 'bar',
         data: {
             labels: ["Что-то 1", "Что-то 2", "Что-то 3", "Что-то 4", "Что-то 5"],
             datasets: [{
                 label: 'усредненный балл',
                 data: [5, 4, 2, 4, 3],
                 backgroundColor: ['#00FFD1', '#00FFD1CC', '#00FFD199', '#00FFD166', '#00FFD133']
             }]
         },
         options: {
             layout: {
                 padding: {
                     top: 24
                 }
             },
             plugins: {
                 legend: {
                     display: false
                 },
                 tooltip: {
                     enabled: false
                 },
                 datalabels: {
                     anchor: 'end',
                     align: 'top',
                     font: {
                         size: '12px'
                     }
                 }
             },
             scales: {
                 y: {
                     grid: {
                         display: false
                     },
                     ticks: {
                         display: false // beginAtZero: true,

                     }
                 },
                 x: {
                     grid: {
                         display: false
                     },
                     ticks: {
                         display: false
                     }
                 }
             }
         }
        }),
        diagrammRed = document.getElementById("statisticsDiagrammRed"),
        diagrammRedInit = new Chart(diagrammRed, {
         type: 'bar',
         data: {
             labels: diagramByScoresLabels,
             datasets: [{
                 label: 'средний балл',
                 data: diagramByScoresData,
                 backgroundColor: ['#4285f4', '#ea4335', '#fbbc04', '#34a853'],
                 maxBarThickness: 8
             }]
         },
            options: {
                layout: {
                    padding: {
                        top: 24
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    /*tooltip: {
                        enabled: false
                    },*/
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        font: {
                            size: '12px'
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            display: true // beginAtZero: true,

                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            display: false
                        }
                    }
                }
            }
        });
}