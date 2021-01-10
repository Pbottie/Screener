<html>
<head>
<meta charset="utf-8"/>
<title>Magic Mirror</title>

<link rel="stylesheet" href="css/main.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/weather-icons.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/knockout/knockout-3.0.0.js"></script>
<script src="js/moment.min.js"></script>

<script type="text/javascript">
	var gitHash = '<?php echo trim(`git rev-parse HEAD`) ?>';
</script>

</head>
<body>

<div class="top left"><div class="small dimmed" data-bind="text: date"></div><div data-bind="html: times"></div></div>
	
 <div class="bottom center-hor xxsmall" data-bind="text: piholeData">
  
  </div>
		
	<div class="top right">
		<div class="small dimmed">
		<span class="wi wi-strong-wind xdimmed"></span>
		<span data-bind="text: windSpeed"></span>
		<span data-bind="css: suns"></span>
		<span data-bind="text: sunTime"></span>
		</div>
		<div>
			<span data-bind="css: iconClass" class="icon dimmed wi"></span>
			 <span class="temp" data-bind="html: temps"></span>
			<span data-bind="html: feels" class="temp small"></span>
		</div>
	</div>

	<div class="bottom left small" data-bind="foreach: buses">
	<p data-bind="html: $data.timeTable">.</p>
	</div>
	<div class="bottom right small">
	<p data-bind="text: tibber">.</p>
	</div>
	<div class="bottom right xxsmall" data-bind="text: SSID">
	N/a
	</div>	
	
	
<script>

function AppViewModel() {
    self = this;
	
	this.times  = ko.observable("");
	this.date = ko.observable("");
	this.weatherData = ko.observable(null);
	this.piholeData = ko.observable("PiholeData");
    
	this.sunTime = ko.observable(); 
	
	self.buses = ko.observableArray();	
	self.tibber =ko.observable("No price info");
	
	this.SSID = ko.observable("N/A");	
	this.ssidUpdate = function()
	{

		$.get("SSID", function(data){
			self.SSID(data.toUpperCase());
		});
	}
	setInterval(this.ssidUpdate, 60000);
	
	this.getTibber = function()
	{
		$.getJSON("tibberkey.php" ,function(result) {


		var pris = result.data.viewer.homes[0].currentSubscription.priceInfo.current.total;

		self.tibber("Elpris: " + (pris*100).toFixed(2) + " öre/kWh");
		});
	

	}

	setInterval(this.getTibber,1800000);

	//OAuth Token Begin
	
	var token;
		
	this.updateToken = function()
	{
		
	$.getJSON("key.php" ,function(result) {

		token = result.access_token;
		
		});
	};
	setInterval(this.updateToken, 18000);
		
	//OAuth Token End
	
	
	
	this.updateBus = function()
	{
	
	var today = new Date();
	var year = today.getFullYear();
	var month = today.getMonth()+1;
	var day = today.getDate();
	var hours = today.getHours();
	var minutes = today.getMinutes();
	
	var idag = year + "-" + month + "-" + day;
	var tid = hours + ":" + minutes;
		
	var tripQuestion =   'https://api.vasttrafik.se/bin/rest.exe/v2/trip?originId=.bohus&destId=.göteborg-central&date=' + idag + '&time=' + tid + '&format=json';
	
	//Olivedal
 	$.ajaxSetup({
		headers : {
			'Authorization' : 'Bearer '+ token,
		}
	});		
		
		
	$.getJSON( tripQuestion,function(result) {

		
		self.buses.removeAll();
       		$.each(result.TripList.Trip, function(i, data) {
	
		var trainTime;
		

               if(i==8 || data.Leg.Origin == null )
                {
                        return false;
                }


		if(data.Leg.Origin.rtTime != null){
			trainTime= data.Leg.Origin.rtTime;
		}else{
			trainTime = data.Leg.Origin.time;
		}
		
		
		        self.buses.push({timeTable:  '<span style="background-color:' 
		        + data.Leg.fgColor + '">' + '<font color="black">' 
		        + data.Leg.name + " " 
		        + trainTime + " "
		        + data.Leg.direction
		        + "</font>"});
			
		
			
		});
	
	
  	 });// END Olivedal
		
	
	};
	
	
	
	
	setInterval(this.updateBus, 20000);
	
	//setInterval(this.update761, 20000);
	
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




	 this.feels = ko.computed(function()
                {
                        if(self.weatherData() == null){
                                return 0 + '&deg;';
                        }else{
                                return '(' + Math.round(self.weatherData().main.feels_like*10)/10 +'&deg;' + ')';
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
				return "wi wi-sunrise xdimmed";

			}
		}		
	});
 	
	this.updateClock = function(){
		var now = moment();
		var dates = now.format('LLLL').split(' ',4);
		self.date(dates[0] + ' ' + dates[1] + ' ' + dates[2] + ' ' + dates[3]);
		var times = now.format('HH') + ':' + now.format('mm') + '<span class="sec">'+now.format('ss')+'</span>';
		self.times(times);		
	};

	setInterval(this.updateClock,999);
	
	this.getPihole = function(){
		
		$.getJSON("PIHOLE.txt", function(data){				
			
			//console.log("PiHole has blocked " + data.ads_blocked_today + " ads today!");
			
			self.piholeData("PiHole has blocked " + data.ads_blocked_today + " ads in the last 24 hours!");
		}
		     );
	}
	setInterval(this.getPihole,20000);
	
	
	//GET Weather
	
	
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
	
	var weatherParams = {
    	'q':'Surte,Sweden',
    	'units':'metric',
	'APPID':'74c81ec9b5ecd02d92f244cf23235856',

    	};
		
	this.updateCurrentWeather = function()
	{
		$.getJSON("https://cors-anywhere.herokuapp.com/http://api.openweathermap.org/data/2.5/weather?q=Surte,Sweden&units=metric&APPID=74c81ec9b5ecd02d92f244cf23235856", function(data){
			self.weatherData(data);
		});

	};

	this.updateCurrentWeather();
	setInterval(this.updateCurrentWeather, 1020000 );
	//END of Weather
	
	//SELFUPDATER
	this.checkVersion = function()
	{
		$.getJSON('githash.php', {}, function(json, textStatus) {
			if (json) {
				if (json.gitHash != gitHash) {
					window.location.reload();
					window.location.href=window.location.href;
				}
			}
		});
		
	};
	setInterval(this.checkVersion, 5000);

	this.getTibber();
	
}
ko.applyBindings(new AppViewModel());
</script>



</body>
</html>
