<html>
<?php

?>


<head>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>



<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>


<script>
	/************************************************************************
		   Let's figure some stuff out first
	************************************************************************/
	totalCalculations = 0
	
	// Setting the 'time' for the model
	// It will start at 9:30 yesterday and increment as if in real time
	stockDate = new Date()
	stockDate.setDate(stockDate.getDate() -4)
	stockDate.setHours(15)
	stockDate.setMinutes(30)
	stockDate.setSeconds(0)
	

	google.charts.load('current', {'packages':['corechart']});
	var stockSymbols = []
	var gr
	var stockData
	
	
	// Loads the list of symbols from a json data file
	function getSymbol()
	{
		console.log("getting stock data...")
		$.post("getStockData.php",{}, function(response) {
			
			stockData = response.data
			//console.log(response)
			loadComplete = true
			countTime()
			symbolLoadComplete()
		},"json")
		
		.fail(function(response)
		{
			console.log(response)
		})
	}
	
	
	// Iterates through each symbol
	function symbolLoadComplete()
	{	


		//console.log(stockData)
		console.log("about to execute loop "+stockData.length+" times")
		
		// stockData.length
		for (y=0; y<stockData.length; y++)
		{
			//console.log(y)
			//console.log(stockData[y][0])
			
			
			if (stockData[y][1] != "No data")
			{
				//console.log(y)
				//console.log(JSON.parse(stockData[y][1]))
				getStockUpdate(stockData[y][0],JSON.parse(stockData[y][1]),JSON.parse(stockData[y][2]))
			}
			
		}
		
		console.log("done loop")
		if (stockDate.getHours() < 18)
		{
			setTimeout(symbolLoadComplete, 60000);
		}
	}
	
	
	// Adds a new stock card to the dashboard
	function addStockCard(data, rsi, symbol, firstPrice, currentPrice, currentRsi)
	{
		
		timeSeriesData = []
		timeSeriesData2 = []
		timeSeriesData3 = []
		//console.log(data)
		for (i in data)
		{
			if (!isTimeInFuture(i))
			{
				num = parseFloat(data[i]["1. open"])
				vol = parseFloat(data[i]["5. volume"])
				timeSeriesData.unshift([i,num])
				timeSeriesData3.unshift([i,vol])
			}
		}
		
		for (i in rsi)
		{
			if (!isTimeInFuture(i))
			{
				num = parseFloat(rsi[i]["RSI"])
				//vol = parseFloat(data[i]["5. volume"])
				timeSeriesData2.unshift([i,num])
				//timeSeriesData2.unshift([i,vol])
			}
		}
		
		timeSeriesData.unshift(['Time', 'Price'])
		timeSeriesData2.unshift(['Time', 'RSI'])
		timeSeriesData3.unshift(['Time', 'Vol'])
		//console.log(timeSeriesData)
		
		  var data = google.visualization.arrayToDataTable(timeSeriesData);
		  var data2 = google.visualization.arrayToDataTable(timeSeriesData2);
		  var data3 = google.visualization.arrayToDataTable(timeSeriesData3);

        var options = {
			hAxis: { textPosition: 'none' },
			legend: {position: 'none'}
        };
		var options2 = {
			hAxis: { textPosition: 'none' },
			legend: {position: 'none'},
			series: {
				0: { color: 'green' }
			}
        };
		var options3 = {
			hAxis: { textPosition: 'none' },
			legend: {position: 'none'},
			series: {
				0: { color: 'red' }
			}
        };
		
		
		
		//console.log()
		//gr = response
		
		if ($("#stockCard_"+symbol).length == 0)
		{
			$("#stockToWatch").append("<div class=\"stockCard\" id=\"stockCard_"+symbol+"\"><div>")
		}


		output = "<div style=\"padding: 4px\"><span id=\"popUp"+symbol+"\" class=\"lgTxt\"><a href=\"#ex1\" rel=\"modal:open\">"+symbol+"</a></span><span class=\"closeButton\" id=\"close"+symbol+"\" style=\"float: right\"><img src=\"close-icon.png\" height=\"20\" width=\"20\"></span></div>"

		output += "<div class=\"chart_div\" id=\"chart_div_"+symbol+"\" style=\"width: 100%; height: 200px;\"></div>"
		output += "<div class=\"chart_div2\" id=\"chart_div2_"+symbol+"\" style=\"width: 100%; height: 200px;\"></div>"
		output += "<div class=\"chart_div3\" id=\"chart_div3_"+symbol+"\" style=\"width: 100%; height: 200px;\"></div>"
		
		output += "<div class=\"center\"><table class=\"table-striped\"><tr><td><span class=\"txt\">Open</span></td><td><span class=\"txt\">$ "+firstPrice+"</span></td></tr>"
		
		
		
		bg = "bg-danger"
		if (instantPriceDelta > 0)
		{
			bg = "bg-success"
		}
		output += "<tr class=\""+bg+"\"><td><span class=\"txt\">Instant $ Change</span></td><td><span class=\"txt\">"+(instantPriceDelta*100).toFixed(2)+" %</span></td></tr>"
		
		
		
		bg = "bg-danger"
		if (dailyPriceDelta > 0)
		{
			bg = "bg-success"
		}
		output += "<tr class=\""+bg+"\"><td><span class=\"txt\">Daily $ Change</span></td><td><span class=\"txt\">"+(dailyPriceDelta*100).toFixed(2)+" %</span></td></tr>"
		
		
		bg = "bg-danger"
		if (instantVolumeDelta > 0)
		{
			bg = "bg-success"
		}
		output += "<tr class=\""+bg+"\"><td><span class=\"txt\">Instant V Change</span></td><td><span class=\"txt\">"+(instantVolumeDelta*100).toFixed(2)+" %</span></td></tr>"
		
		
		bg = "bg-danger"
		if (dailyVolumeDelta > 0)
		{
			bg = "bg-success"
		}
		output += "<tr class=\""+bg+"\"><td><span class=\"txt\">Daily V Change</span></td><td><span class=\"txt\">"+(dailyVolumeDelta*100).toFixed(2)+" %</span></td></tr>"
		
		
		
		bg = "bg-danger"
		if (currentRsi < 30)
		{
			bg = "bg-success"
		}
		output += "<tr class=\""+bg+"\"><td><span class=\"txt\">RSI</span></td><td><span class=\"txt\">"+currentRsi+" </span></td></tr>"
		
		
		output += "<tr><td><span class=\"txt\">Current</span></td><td><span class=\"txt\">$ "+currentPrice+"</span></td></tr>"
		
		
		
		$("#stockCard_"+symbol).html(output).hide().fadeIn(1000)
		var chart = new google.visualization.AreaChart(document.getElementById('chart_div_'+symbol+''));
		var chart2 = new google.visualization.AreaChart(document.getElementById('chart_div2_'+symbol+''));
		var chart3 = new google.visualization.AreaChart(document.getElementById('chart_div3_'+symbol+''));
        chart.draw(data, options);
		chart2.draw(data2, options2);
		chart3.draw(data3, options3);
	
		//$(".chart_div2").
		
		$(".lgTxt").off()
		$(".lgTxt").on("click", function()
		{
			console.log("get info on "+ $(this).html())
			
			symbol = $(this).attr("id").replace("popUp","")
			console.log("https://www.alphavantage.co/query?function=OVERVIEW&symbol="+$(this).attr("id").replace("popUp","")+"&apikey=79H81T3WZM0Q6NF4")
			$.getJSON("https://www.alphavantage.co/query?function=OVERVIEW&symbol="+$(this).attr("id").replace("popUp","")+"&apikey=79H81T3WZM0Q6NF4", function(response)
				{
					//console.log(response)
					//$("#popUpContent").html(symbol)
					
					output2 = "<div><table>"
					
					for (attr in response)
					{
						output2 += "<tr><td>"+attr+"</td><td>"+response[attr]+"</td>"
					}
					
					output2 += "</table></div>"
					
					$("#popUpContent").html("")
					$("#popUpContent").append(output2)
				})
			
		})
		
		$(".closeButton").off()
		$(".closeButton").on("click", function() {
			$("#stockCard_"+$(this).attr("id").replace("close","")).fadeOut(200, function() {
				$("#stockCard_"+$(this).attr("id").replace("close","")).remove()
			})
			
		})
		
	}
	

	// https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=IBM&interval=5min&apikey=79H81T3WZM0Q6NF4
	
	
	
	// Evaluate if the current time is in the future or not the correct date
	function isTimeInFuture(time)
	{
		currentTime = new Date(time)
		//console.log("Current date - " + currentTime.getDate())
		//console.log("Stock Date - " + stockDate.getDate())
		//console.log(currentTime + " - " + (currentTime.getDate() != stockDate.getDate()))
		//console.log(currentTime.getDate() + " ?= " + stockDate.getDate())
		if (currentTime > stockDate || (currentTime.getDate() != stockDate.getDate()))
			return true
		else
			return false
	}
	
	
	function getStockUpdate(symbol,data,rsi)
	{
		
		currentPrice = 0
		openPrice = 0
		lastPrice=0
		currentVolume=0
		lastVolume=0
		openVolume=0
		currentRsi=0
		lastRsi=0
		x=0
		
		
		totalCalculations++
		$("#ppiCount").html(totalCalculations)
		
		//console.log(symbol)
		//console.log(data)
		
		//console.log(Object.keys(data))
		for (i in data)
		{	
			//console.log(i + " - " + !isTimeInFuture(i))
			if (!isTimeInFuture(i))
			{
				if (x==0)
				{
					currentPrice = data[i]["1. open"]
					currentVolume = data[i]["5. volume"]
					//console.log("open Price = "+openPrice)
				}
				if (x==1)
				{
					lastPrice = data[i]["1. open"]
					lastVolume = data[i]["5. volume"]
				}

				openPrice = data[i]["1. open"]
				openVolume = data[i]["5. volume"]
				
				x++
				//console.log("current price = "+currentPrice)
			}
		}
		
		z=0
		for (i in rsi)
		{
			if (!isTimeInFuture(i))
			{
				if (z == 0)
				{
					currentRsi = rsi[i]["RSI"]
				}
				if (z == 1)
				{
					lastRsi = rsi[i]["RSI"]
				}
				openRsi = rsi[i]["RSI"]
				z++
			}
		}
		
		instantPriceDelta= ((currentPrice - lastPrice) / lastPrice)
		dailyPriceDelta = ((currentPrice - openPrice) / openPrice)
		
		instantVolumeDelta= ((currentVolume - lastVolume) / lastVolume)
		dailyVolumeDelta = ((currentVolume - openVolume) / openVolume)
		
		instantRsiDelta = (currentRsi - lastRsi) / lastRsi
		//console.log(instantRsiDelta)
		
		if (currentPrice !=0 && openPrice > 0 && x > 1 && lastRsi != 0)
		{				
			
			if (isThisStockInteresting(dailyPriceDelta, instantPriceDelta, instantVolumeDelta, dailyVolumeDelta, symbol, currentRsi,instantRsiDelta) == true || $("#stockCard_"+symbol).length > 0)
			{
				console.log("-----"+symbol+"-----")
				//console.log(currentRsi)
				addStockCard(data, rsi, symbol, openPrice, currentPrice, currentRsi);
			}
		}
		else
		{
			//console.log(symbol + "did not meet criteria to calculate. (not enough data points?)")
			//console.log(data)
		}
	}
	

	
	$(function() {
		$("#title").on("click",function()
		{
			//getSymbol()
		})
	});
	
	// Determine if this stock is worth watching on the dashboard
	function isThisStockInteresting(dailyPriceDelta, instantPriceDelta, instantVolumeDelta, dailyVolumeDelta, symbol,currentRsi,instantRsiDelta)
	{		

		if (instantPriceDelta > .2 || dailyPriceDelta > .2)
		{
			return true
		}
		else
		{
			return false
		}
	}
	
	function countTime()
	{
		if (stockDate.getHours() < 18)
		{
			stockDate.setSeconds(stockDate.getSeconds() + 1)
			$("#theTime").html(stockDate)
			setTimeout(countTime, (1000 / $("#speed").val()))
		}
	}
	
	getSymbol()
	

</script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link rel="stylesheet" href="styles.css">
<body>


<div class="mainContainer">
		<h1 id="title">Stock Ninja</h1>
		<div id="theTime" class="medTxt"></div>
		<div class="container">
			
			<div class="container" id="">			
				<table class="table-striped">
					<tr>
						<td>Count</td>
						<td><span id="ppiCount">0</span></td>
						<td>Speed: <input type="number" value="1" id="speed" min="1" max="20" step="1"></td>
					</tr>

				</table>
			</div>
			
			<div class="container" id="stockToWatch">			
			</div>

		</div>
		
		</div>
	</div>
	
	
	
	<!-- Modal HTML embedded directly into document -->
<div id="ex1" class="modal">
 <div id="popUpContent" class="popUpContent"></div>
  <a href="#" rel="modal:close">Close</a>
</div>



<!-- jQuery Modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />

</html>