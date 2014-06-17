<html>
<head>
<title>Magic Mirror</title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/knockout/knockout-3.0.0.js"></script>
<script src="js/moment.min.js"></script>
</head>
<body>


	<div class="top left"><div class="small dimmed" data-bind="text: date"></div><div data-bind="html: times"></div><div class="calendar xxsmall"></div></div>
	
	<div class="top right">
		<div class="windsun small dimmed" data-bind="html: icons">
		</div>
		<div class="temp">
			<span data-bind="css: iconClass, text: temp" class="icon dimmed wi">
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
	
	this.icons = ko.observable('');
	
	this.iconClass = ko.computed(function(data)
	{
		return iconTable[self.weatherData.weather[0].icon];
	});
	
	//this.temps  = ko.observable("");
	this.temps = ko.computed(function(data)
		{
			return self.weatherData.main.temp;
		});
		
	
	this.times  = ko.observable("");
	this.date = ko.observable("");
	
	this.weatherData = ko.observable();
	
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
	$.getJSON('http://api.openweathermap.org/data/2.5/weather', weatherParams, weatherData)
	/*	$.getJSON('http://api.openweathermap.org/data/2.5/weather', weatherParams, function(json, textStatus) {

			var temp = Math.round(json.main.temp);
			var temp_min = json.main.temp_min;
			var temp_max = json.main.temp_max;
			var wind = Math.round(json.wind.speed);

			var iconClass = iconTable[json.weather[0].icon];
			
			var iconText = '<span class=" icon dimmed wi ' + iconClass + '"></span>'
				
			//$('.temp').updateWithText(icon.outerHTML()+temp+'&deg;', 1000);
			
			self.temps( iconText + temp + '&deg;');

			var now = new Date();
			var sunrise = new Date(json.sys.sunrise*1000).toTimeString().substring(0,5);
			var sunset = new Date(json.sys.sunset*1000).toTimeString().substring(0,5);
			
			var windString = '<span class="wi wi-strong-wind xdimmed"></span> ' + wind ;
			var sunString = '<span class="wi wi-sunrise xdimmed"></span> ' + sunrise;
			if (json.sys.sunrise*1000 < now && json.sys.sunset*1000 > now) {
				sunString = '<span class="wi wi-sunset xdimmed"></span> ' + sunset;
			}
			self.icons(windString + ' ' + sunString);
		});*/

	};
	this.updateCurrentWeather();
	setInterval(this.updateCurrentWeather, 60000);
	//END of Weather


}
ko.applyBindings(new AppViewModel());
</script>



</body>
</html>

