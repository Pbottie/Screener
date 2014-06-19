<html>
<head>
<title>Magic Mirror</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/knockout/knockout-3.0.0.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/knockout.mapping.js"></script>
</head>
<body>

	<div class="top left"><div class="small dimmed" data-bind="text: date"></div><div data-bind="html: times"></div></div>
	
	<div class="top right">
		<div class="small dimmed">
		<span class="wi wi-strong-wind xdimmed"></span>
		<span data-bind="text: windSpeed"></span>
		<span data-bind="css: suns"></span>
		<span data-bind="text: sunTime"></span>
		</div>
		<div>
			<span data-bind="css: iconClass" class="icon dimmed wi"></span>
			<span class="temp" data-bind="html: temps">
		</div>
	</div>

	<div class="bottom left small light">
	<p id="0">.</p>
	<p id="1">.</p>
	<p id="2">.</p>
	<p id="3">.</p>
	</div>


<script>
$(document).ready(function () {

    /*	For trip call (from/to)
    	var tripQuestion = 'https://api.vasttrafik.se/bin/rest.exe/v1/trip?originId=.kvil&destId=.anekd&format=json&jsonpCallback=tripSearch&authKey=5914945f-3e58-4bbc-8169-29571809775d&needJourneyDetail=0';
    */
    var tripQuestion = 'https://api.vasttrafik.se/bin/rest.exe/v1/departureBoard?id=.kvil&format=json&jsonpCallback=?&direction=.anekd&authKey=5914945f-3e58-4bbc-8169-29571809775d&needJourneyDetail=0&timeSpan=1439&maxDeparturesPerLine=4';
    $.getJSON( tripQuestion,function(result) {
        var bus =[];
        var time=[];
		
        $.each(result.DepartureBoard.Departure, function(i, data) {
            bus.push(data.name);
            time.push(data.rtTime);
            //Now Add Bus and Times to page and/or time left
            var textField = "#" + i.toString();
            $(textField).text(bus[i] + " " + time[i]);
        });
    });

});
function AppViewModel() {
    self = this;
	
	
	this.times  = ko.observable("");
	this.date = ko.observable("");
	this.weatherData = ko.observable(null);
				
	this.sunTime = ko.observable(); 
	
	
	this.windSpeed = ko.computed(function()
	{		
		if(self.weatherData() == null){
		return 'N/a';
		}
		else{
		return Math.round(self.weatherData().wind.speed);
		}
	});

	this.iconClass = ko.computed(function()
	{		
		if(self.weatherData() == null){
		return "wi-day-sunny";
		}
		else{
		return iconTable[self.weatherData().weather[0].icon];
		}
	});
	
	this.temps = ko.computed(function()
		{
			if(self.weatherData() == null){
				return 0 + '&deg;';
			}else{
				return Math.round(self.weatherData().main.temp*10)/10 +'&deg;';
			}
		});
	
	this.suns = ko.computed(function()
	{
		var now = new Date();
		
		if(self.weatherData() == null){
		return "wi-day-sunny";
		}
		else{
			if (self.weatherData().sys.sunrise*1000 < now && self.weatherData().sys.sunset*1000 > now) {
				self.sunTime(new Date(self.weatherData().sys.sunset*1000).toTimeString().substring(0,5));
				return "wi wi-sunset xdimmed";

			}
			else{
				self.sunTime(new Date(self.weatherData().sys.sunrise*1000).toTimeString().substring(0,5));
				console.log(self.sunTime);
				return "wi wi-sunrise xdimmed";

			}
		}		
	});
	
	("wi wi-sunset xdimmed");	
	
 
	
	
	this.updateClock = function(){
	
	var now = moment();
	var dates = now.format('LLLL').split(' ',4);
	
	self.date(dates[0] + ' ' + dates[1] + ' ' + dates[2] + ' ' + dates[3]);
	var times = now.format('HH') + ':' + now.format('mm') + '<span class="sec">'+now.format('ss')+'</span>';
	self.times(times);
	
	};

	setInterval(this.updateClock,999);
	
	//GET Weather
	var weatherParams = {
    'q':'Gothenburg,Sweden',
    'units':'metric',
    };
	
	var iconTable = {
			'01d':'wi-day-sunny',
			'02d':'wi-day-cloudy',
			'03d':'wi-cloudy',
			'04d':'wi-cloudy-windy',
			'09d':'wi-showers',
			'10d':'wi-rain',
			'11d':'wi-thunderstorm',
			'13d':'wi-snow',
			'50d':'wi-fog',
			'01n':'wi-night-clear',
			'02n':'wi-night-cloudy',
			'03n':'wi-night-cloudy',
			'04n':'wi-night-cloudy',
			'09n':'wi-night-showers',
			'10n':'wi-night-rain',
			'11n':'wi-night-thunderstorm',
			'13n':'wi-night-snow',
			'50n':'wi-night-alt-cloudy-windy'
		};
		
	this.updateCurrentWeather = function()
	{
		$.getJSON('http://api.openweathermap.org/data/2.5/weather', weatherParams, function(data){
			self.weatherData(data);
		});

	};

	this.updateCurrentWeather();
	setInterval(this.updateCurrentWeather, 60000);
	//END of Weather


}
ko.applyBindings(new AppViewModel());
</script>



</body>
</html>

