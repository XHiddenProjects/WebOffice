$(document).ready(function(){
    $.ajax({
        url: `${BASE}/api/users`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            LANGUAGES.then((e)=>{
                // Get created_at column for each user
                const createdAtDates = response.map(user => new Date(user.created_at));
                // Count users per month
                const userCounts = Array(12).fill(0);
                createdAtDates.forEach(date => {
                    const month = date.getMonth(); // 0-11
                    userCounts[month]++;
                });
                const labels = e.dates.months;
                if($('.users-count-chart').length){
                    Plotly.newPlot($('.users-count-chart')[0],[{
                        x: labels,
                        y: userCounts,
                        type: 'bar',
                        fill: 'tozeroy',
                        line: {color: 'rgba(75, 192, 192, 1)'}
                    }],{
                        title: {text: e.charts.users._title||'User Registrations Per Month'},
                        xaxis: {title: {text: e.charts.users.X_axis||'Month'}},
                        yaxis: {title: {text: e.charts.users.Y_axis||'Number of Users'}, rangemode: 'tozero', zeroline: true, zerolinecolor: '#000'}
                    });
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching user count:', error);
        }
    });


    const cpuChartLabels = {},
    randColors = ['red','orange','yellow','lime','green','cyan','blue','purple'];
    LANGUAGES.then((e)=>{
        cpuChartLabels.title = e.charts.cpu._title||'CPU Usage Over Time';
        cpuChartLabels.xaxis = e.charts.cpu.X_axis||'Time (seconds)';
        cpuChartLabels.yaxis = e.charts.cpu.Y_axis||'Usage (%)';
    });

    updateCpuChart([0]);

    function updateCpuChart(percentages) {
        // Ensure Plotly is loaded and chart element exists
        if (typeof window.Plotly === 'undefined') {
            console.error('Plotly library is not loaded.');
            return;
        }

        const chartElem = $('.cpu-chart')[0];
        if (!chartElem) {
            console.error('Chart element not found.');
            return;
        }

        // Number of CPUs based on percentages array length
        const cpuCount = Array.isArray(percentages) ? percentages.length : 0;

        // Calculate current frequencies based on usage percentages
        const cpuFrequencies = percentages;

        // Initialize storage for frequency history if not present
        if (!window.cpuFreqHistory) {
            window.cpuFreqHistory = Array(cpuCount).fill().map(() => [{ x: 0, y: 0 }]);
            window.timePoints = [0]; // Initialize time points
        } else if (window.cpuFreqHistory.length !== cpuCount) {
            // Reset if CPU count changes
            window.cpuFreqHistory = Array(cpuCount).fill().map(() => [{ x: 0, y: 0 }]);
            window.timePoints = [0];
        }

        // Append new frequency data for each CPU
        const lastTime = window.timePoints[window.timePoints.length - 1];
        const newTime = lastTime + 5; // Time step, adjust as needed
        window.timePoints.push(newTime);
        if (window.timePoints.length > 30) {
            window.timePoints.shift();
        }

        cpuFrequencies.forEach((freq, idx) => {
            // Append new data point
            window.cpuFreqHistory[idx].push({ x: newTime, y: freq });
            // Keep only last 30 data points
            if (window.cpuFreqHistory[idx].length > 30) {
                window.cpuFreqHistory[idx].shift();
            }
        });

        // Prepare data traces for each CPU
        const traces = window.cpuFreqHistory.map((freqHistory, idx) => ({
            x: freqHistory.map(point => point.x),
            y: freqHistory.map(point => point.y),
            type: 'scatter',
            mode: 'lines',
            name: `CPU ${idx + 1}`,
            fill: 'tozeroy',
            line: { 
                shape: 'spline',
                color: randColors[idx % randColors.length] 
            }
        }));

        // Determine x-axis range to always show the last 30 seconds
        const maxTime = Math.max(...window.timePoints);
        const windowSize = 30; // seconds
        const xRangeStart = Math.max(0, maxTime - windowSize);
        const xRangeEnd = maxTime;

        // Define layout with fixed x-axis range
        const layout = {
            title: {
                text: cpuChartLabels.title || 'CPU Usage Over Time',
            },
            xaxis: {
                title: {
                    text: cpuChartLabels.xaxis || 'Time (seconds)',
                },
                type: 'linear',
                range: [xRangeStart, xRangeEnd],
                zeroline: true,
                zerolinecolor: '#000',
                tick0: xRangeStart,
                dtick: 5
            },
            yaxis: {
                title: {
                    text: cpuChartLabels.yaxis || 'Usage (%)',
                },
                zeroline: true,
                zerolinecolor: '#000'
            }
        };

        // Initialize or update the plot
        if (!window.cpuChartInitialized) {
            Plotly.newPlot(chartElem, traces, layout);
            window.cpuChartInitialized = true;
        } else {
            Plotly.react(chartElem, traces, layout);
        }
    }
    
    setInterval(()=>{
        $.ajax({
            url: `${BASE}/vendor/cpu.php`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const percentage = response.percent;
                updateCpuChart(percentage);
            },
            error: function(error) {
                console.error('Error fetching CPU data:', error);
            }
        });
    }, 5000);


    $.ajax({
        url: `${BASE}/vendor/memory.php`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            LANGUAGES.then((e)=>{
                const memory = $('.memory-chart')[0],
                memoryData = response.memory,
                swapData = response.swap_memory,
                customData = [memoryData.used,memoryData.available,swapData.used,swapData.free].map(val => Generators.parseBytes(val));

                // Prepare data for the pie chart
                const data = [{
                    type: "pie",
                    labels: [
                        `${(e.charts.memory.memoryUsed||"Memory Used")}`, 
                        `${(e.charts.memory.memoryAvailable||"Memory Available")}`,
                        `${(e.charts.memory.swapUsed||"Swap Used")}`,
                        `${(e.charts.memory.swapFree||"Swap Free")}`
                    ],
                    values: [
                        memoryData.used,
                        memoryData.available,
                        swapData.used,
                        swapData.free
                    ],
                    textinfo: "label+percent",
                    insidetextorientation: "radial",
                    customdata: customData,
                    hovertemplate: '%{label}\n%{customdata}<extra></extra>',
                }];

                const layout = {
                    title: {
                        text: e.charts.memory?._title || "Memory and Swap Usage",
                    },
                    hovermode: 'closest'
                };

                // Render the pie chart
                Plotly.newPlot(memory, data, layout);
            });
        },
        error: function(error) {
            console.error('Error fetching Memory data:', error);
        }
    });

    LANGUAGES.then((e) => {
        let defaultDownloadData = [{
            type: "indicator",
            mode: "gauge+number",
            value: 0,
            title: { 
                text: e.internet.downloadSpeed || 'Download Speed'
            },
            gauge: {
                axis: {
                    range: [0, 1000],
                    tickvals: [0, 50, 100, 250, 500, 750, 1000],
                    ticktext: ["0", "50", "100", "250", "500", "750", "1000"]
                },
                bar: { color: "blue" }
            },
            number: { suffix: " Mb/s" }
        }];

        let defaultUploadData = [{
            type: "indicator",
            mode: "gauge+number",
            value: 0,
            title: { 
                text: e.internet.uploadSpeed || "Upload Speed"
            },
            gauge: {
                axis: {
                    range: [0, 1000],
                    tickvals: [0, 50, 100, 250, 500, 750, 1000],
                    ticktext: ["0", "50", "100", "250", "500", "750", "1000"]
                },
                bar: { color: "green" }
            },
            number: { suffix: " Mb/s" }
        }];

        let defaultLayout = {
            width: 400,
            height: 300,
            margin: { t: 50, b: 0 }
        };

        // Plot placeholder charts
        Plotly.newPlot($('.downloadIndicator')[0], defaultDownloadData, defaultLayout);
        Plotly.newPlot($('.uploadIndicator')[0], defaultUploadData, defaultLayout);
    });

    // AJAX call to update the gauges with real data
    $.ajax({
        url: `${BASE}/vendor/internetSpeed.php`,
        dataType: "json",
        method: "GET",
        success: function(results) {
            var downloadValue = results.download.value;
            var uploadValue = results.upload.value;

            Plotly.update($('.downloadIndicator')[0], {
                value: [downloadValue]
            });

            Plotly.update($('.uploadIndicator')[0], {
                value: [uploadValue]
            });
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data:", error);
        }
    });

    setInterval(()=>{
        $.ajax({
            url:`${BASE}/vendor/battery.php`,
            dataType:"JSON",
            method:"GET",
            success:function(results){
                const battery = $('.battery'),
                percent = results.percent;

                if(percent<30)
                    battery.attr('battery-status','low');
                else if(percent>=30&&percent<70)
                    battery.attr('battery-status','ok');
                else if(percent>=70&&percent<90)
                    battery.attr('battery-status','good')
                else
                    battery.attr('battery-status', 'excellent');

                if(battery.find('.battery-percent').length>0)
                    battery.find('.battery-percent').text(`(${Math.floor(percent)}%)`);

                if(battery.hasClass('battery-horizontal')){
                    battery.find('.charge').css({
                        'width':`${Math.floor(percent)}%`,
                        'height':`100%`
                    });
                }else{
                    battery.find('.charge').css({
                        'height':`${Math.floor(percent)}%`,
                        'width':'100%'
                    });
                }
                if(results.plugged_in){
                    battery.find('i').css({
                        opacity: 1
                    })
                }else{
                    battery.find('i').css({
                        opacity: 0
                    })
                }
            }
        })
    },1500);

});