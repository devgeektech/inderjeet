jQuery(function ($) {
    var map;
    var originPlaceId = '';
    var travelMode = google.maps.TravelMode.DRIVING;
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true
    });
    var pinA;
    var destinationLat;
    var destinationLng;
    var isDefaultOrigin = false;

    function initMapData() {
        initMap();
        var loc_id = getParameterByName('id');
        jQuery.ajax({
            type: "POST",
            url: '/locate-us/get-map-data',
            async: true,
            dataType: 'json',
            data: {id: loc_id}
        }).done(function (json) {
            initMap(json);
            jQuery('#map').removeClass('loading');
        });
    }

    function initMap(json) {
        if (typeof (google) == 'undefined') {
            return;
        }
        if (typeof (jQuery) == 'undefined') {
            return;
        }

        var geocoder = new google.maps.Geocoder();

        map = new google.maps.Map(document.getElementById('map'), {
            minZoom:2,
		    maxZoom: 17,
		    center: new google.maps.LatLng(1.319, 103.8948),
        });

        CaculateRoute(map);

        google.maps.event.addDomListener(document.getElementById('go'), 'click',
            function () {
                if (jQuery('#origin-input').val()) {
                    if (typeof route() === 'function') {
                        showRoute(true, true);
                    }
                }
                else {
                    $('#panel-error').text('Please enter your starting location');
                    $('#panel-error').show();
                }
            });

        google.maps.event.addDomListener(document.getElementById('back'), 'click',
            function () {
                showRoute(false, true);
            });

        $('.btn-close-form').on('click', function () {
            jQuery('#locate-us .list-location').slideDown();
            jQuery('#locate-us .right-info').slideUp();
        });

        jQuery('.go-to-step1').on('click', function () {
            jQuery('#locate-us').css('display','none');
            jQuery('#step-0').css('display','block');
        });

        if (!jQuery.isEmptyObject(json)) {
            if ((json.data_map != null) && (json.data_map.length > 0)) {
                var features = [];
                json.data_map.forEach(function (feature) {
                    if (feature.houseBlockNumber === null) {
                        feature.houseBlockNumber = '';
                    }
                    if (feature.postCode === null) {
                        feature.postCode = '';
                    }
                    if (feature.streetName === null) {
                        feature.streetName = '';
                    }

                    var full_address = feature.houseBlockNumber + ' ' + feature.streetName;
                    var custom_address = feature.houseBlockNumber + ' ' + feature.streetName + ', Singapore ' + feature.postCode;
                    if (feature.unitNumber !== null || feature.buildingName !== null) {
                        if (feature.unitNumber === null) {
                            feature.unitNumber = '';
                        }
                        if (feature.buildingName === null) {
                            feature.buildingName = '';
                        }
                        full_address = full_address + ', ' + feature.unitNumber + ' ' + feature.buildingName;
                    }

                    full_address = full_address + ', Singapore ' + feature.postCode;

                    features.push({
                        position: new google.maps.LatLng(feature.latitude, feature.longitude),
                        type: feature.outletType,
                        marker_id: feature.outletId,
                        title: feature.buildingName,
                        outlet_name: feature.outletName,
                        service: feature.service,
                        open_hours: feature.operatingHours,
                        address: full_address,
                        custom_adrress: custom_address,
                    });
                });

                var bounds = new google.maps.LatLngBounds();

                // Create markers.
                var allMarkers = [];
                features.forEach(function (feature) {
                    var marker = new google.maps.Marker({
                        position: feature.position,
                        type_id: feature.type,
                        map: map,
                        title: feature.outlet_name,
                        icon: json.marker,
                        marker_id: feature.marker_id,
                        address: feature.address,
                        custom_adrress: feature.custom_adrress,
                    });

                    allMarkers.push(marker);

                    google.maps.event.addListener(marker, 'click', function (marker, i) {
                        var html = renderLayout(feature);
                        //alert(html);
                        //set data
                        jQuery('#locate-us .locate-content-container').html(html);
                        jQuery('#locate-us .heading-title').text(feature.outlet_name);
                        jQuery('#locate-us .type-location').text(splitCapitalCharacter(feature.type));
                        jQuery('#locate-us .address-location').text(feature.address);
                        jQuery('#locate-us .locate-content-container').html(html);
                        jQuery('#locate-us .list-location').slideUp();
                        jQuery('#locate-us .right-info').slideDown();
                        originPlaceId = jQuery('#place-id-node').val();
                        isDefaultOrigin = true;
                        showRoute(false, true);

                        if (json.data_map.length > 1) {
                            //marker animation
                            for (var i = 0; i < allMarkers.length; i++) {
                                if (allMarkers[i].marker_id != this.marker_id) {
                                    allMarkers[i].setAnimation(null);
                                }
                            }
                            this.setAnimation(google.maps.Animation.BOUNCE);
                            destinationLat = this.getPosition().lat();
                            destinationLng = this.getPosition().lng();
                            jQuery('#destination-input').attr('data-lat', destinationLat);
                            jQuery('#destination-input').attr('data-lng', destinationLng);
                            jQuery('#destination-input').val(this.custom_adrress);
                            map.setZoom(17);
                            jQuery('#open_app_loction').css('display','block');
                        }

                        map.setCenter(this.position);
                    });

                    jQuery('.title-marker').on('click', function () {
                        //alert("IN Clicked");
                        google.maps.event.trigger(allMarkers[jQuery(this).attr('data-markerid')], 'click');
                        jQuery('#locate_us_1').css('display','none');
                        jQuery('#open_app_loction').css('display','block');
                    });

                    bounds.extend(feature.position);

                    if (json.data_map.length == 1) {
                        new google.maps.event.trigger(marker, 'click');
                    }

                });

                if (json.url) {
                    google.maps.event.trigger(allMarkers[0], 'click');
                }

                map.fitBounds(bounds);
            }

            //if user select an address from autocomeplte, use the place_id
            // instead of keyword for accurate marker position
            var bounds = new google.maps.LatLngBounds();
            if (json.place_id !== null) {
                defaultOriginPlaceId = json.place_id;
                var service = new google.maps.places.PlacesService(map);
                service.getDetails({
                    placeId: json.place_id
                }, function (result, status) {
                    var marker = new google.maps.Marker({
                        map: map,
                        position: result.geometry.location,
                        place: {
                            placeId: json.place_id,
                            location: result.geometry.location
                        }
                    });

                    bounds.extend(marker.position);
                    var closest = findClosestLocation(allMarkers, marker);
                    setBoundClosestLocation(bounds, closest, map);
                });
            }
            else {
                //if haven't place id but have keyword, then create place id by
                // query keyword
                if (json.keyword !== null) {
                    var request = {
                        query: json.keyword,
                        fields: ["name", "geometry", "place_id"],
                    };

                    var service = new google.maps.places.PlacesService(map);

                    service.findPlaceFromQuery(request, (results, status) => {
                        if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                            if ((results !== null) && (results.length > 0)) {
                                if (!results[0].geometry || !results[0].geometry.location) {
                                    return;
                                }

                                var marker = new google.maps.Marker({
                                    map: map,
                                    position: results[0].geometry.location,
                                    place: {
                                        placeId: results[0].place_id,
                                        location: results[0].geometry.location
                                    }
                                });

                                jQuery('#place-id-node').val(results[0].place_id);
                                originPlaceId = jQuery('#place-id-node').val();

                                bounds.extend(marker.position);
                                var closest = findClosestLocation(allMarkers, marker);
                                setBoundClosestLocation(bounds, closest, map);
                            }
                        }
                        else {
                            geocoder.geocode({'address': json.keyword + ', Singapore'}, function (results, status) {
                                if (status === 'OK') {
                                    var marker = new google.maps.Marker({
                                        position: results[0].geometry.keyword,
                                        map: map
                                    });

                                    bounds.extend(marker.position);
                                    var closest = findClosestLocation(allMarkers, marker);
                                    setBoundClosestLocation(bounds, closest, map);
                                }
                            })
                        }
                    });
                }
            }
        }
        else {
            geocoder.geocode({'address': 'Singapore'}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);
                }
            });
        }
    }

    function setBoundClosestLocation(bounds, closest_locations, map) {
        if (closest_locations.length) {
            for (i = 0; i < 3; i++) {
                if (closest_locations[i] !== undefined) {
                    bounds.extend(closest_locations[i].location.position);
                }
            }

            map.fitBounds(bounds);
        }
    }

    function findClosestLocation(locationMarkers, currentMarker) {
        var closest = [];
        locationMarkers.forEach(function (location) {
            closest.push({
                distance: google.maps.geometry.spherical.computeDistanceBetween(location.position, currentMarker.position),
                location: location
            });
        });

        closest.sort(function (a, b) {
            return a.distance - b.distance;
        });

        return closest;
    }

    function initAutocomplete() {
        var input = document.getElementsByClassName('map-autocomplete');
        var options = {
            componentRestrictions: {
                country: "sg"
            }
        };

        for (var i = 0; i < input.length; i++) {
            var autocomplete = new google.maps.places.Autocomplete(input[i], options);
            autocomplete.targetID = input[i].getAttribute("target-id");

            autocomplete.addListener('place_changed', function () {
                var place = this.getPlace();
                if (!place.geometry) {
                    return;
                }

                var placeId = place.place_id;

                document.getElementById(this.targetID).value = placeId;
            });
        }

    }

    function renderLayout(location) {
        var html = '';

        //html += '<div class="locate-content-infobox">';
        
        if (location.open_hours) {
            html += '<div class="card active">\n' +
                    '<div class="card-header" id="sgpAccordionOne">\n'+
                '        <button class="sgp-accordion__btn" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">\n' +
                '          Opening Hours\n' +
                '        </button>\n' +
                '    </div>\n' +
                '    <div id="collapseOne" class="collapse show" aria-labelledby="sgpAccordionOne" data-parent="#sgpAccordion">\n' +
                '       <div class="card-body">\n' +
                '           <p class="sgp-accordion__desc">\n' +
                '        ' + formatDataHours(location.open_hours) + '\n' +
                '           </p>\n' +
                '       </div></div>\n' +
                '   </div>';
        }

        if (location.service) {
            html += '<div class="card">\n' +
                    '<div class="card-header" id="sgpAccordionTwo">\n'+
                '        <button class="sgp-accordion__btn collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">\n' +
                '          Services Available\n' +
                '        </button>\n' +
                '    </div>\n' +
                '    <div id="collapseTwo" class="collapse" aria-labelledby="sgpAccordionTwo" data-parent="#sgpAccordion">\n' +
                '       <div class="card-body">\n' +
                '           <p class="sgp-accordion__desc">\n' +
                '        ' + formatDataService(location.service) + '\n' +
                '           </p>\n' +
                '       </div></div>\n' +
                '   </div>';
        }

       // html += '</div>';

        return html;
    }

    function getParameterByName(name, url) {
        if (!url) {
            url = window.location.href;
        }
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) {
            return null;
        }
        if (!results[2]) {
            return '';
        }
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    function CaculateRoute(map) {
        directionsRenderer.setMap(map);
        const originInput = document.getElementById("origin-input");
        var originAutocomplete = new google.maps.places.Autocomplete(originInput);
        originAutocomplete.setFields(["place_id"]);
        setupClickListener(
            "changemode-driving",
            google.maps.TravelMode.DRIVING
        );
        setupClickListener(
            "changemode-walking",
            google.maps.TravelMode.WALKING
        );
        setupClickListener(
            "changemode-transit",
            google.maps.TravelMode.TRANSIT
        );
        setupClickListener(
            "changemode-bicycling",
            google.maps.TravelMode.BICYCLING
        );
        setupPlaceChangedListener(originAutocomplete, "ORIG");
    }

    function setupClickListener(id, mode) {
        const radioButton = document.getElementById(id);
        radioButton.addEventListener("click", () => {
            travelMode = mode;
            showRoute(true);
            route();
        });
    }

    function setupPlaceChangedListener(autocomplete, mode) {

        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();
            isDefaultOrigin = false;

            if (!place.place_id) {
                window.alert("Please select an option from the dropdown list.");
                return;
            }

            if (mode === "ORIG") {
                originPlaceId = place.place_id;
            }
        });
    }

    function route() {
        if (!originPlaceId) {
            return;
        }

        var latlngDestination = {
            lat: parseFloat(destinationLat),
            lng: parseFloat(destinationLng)
        };

        var panelError = jQuery('#panel-error');
        var panel = document.getElementById("panel");

        directionsService.route(
            {
                origin: {placeId: originPlaceId},
                destination: latlngDestination,
                travelMode: travelMode,
            },
            (response, status) => {
                if (status === "OK") {
                    clearMarker();
                    directionsRenderer.setDirections(response);
                    var _route = response.routes[0].legs[0];

                    if (isDefaultOrigin === false) {
                        pinA = new google.maps.Marker({
                            position: _route.start_location,
                            map: map,
                        });
                    }
                    directionsRenderer.setPanel(panel);
                    showRoute(true);
                }
                else if (status === "ZERO_RESULTS") {
                    panelError.text("Directions for " + travelMode.toLowerCase() + " is not available");
                    showRoute(false);
                }
                else if (status === 'NOT_FOUND') {
                    panelError.text("There are no locations found!");
                    showRoute(false);
                }
                else {
                    window.alert("Directions request failed due to " + status);
                }
            }
        );
    }

    function clearMarker() {
        if (pinA) {
            pinA.setMap(null);
        }
    }

    function showRoute(isShow, isClose) {
        if (isShow === false) {
            $('#panel').hide();
            $('#panel-error').show();
            directionsRenderer.setMap(null);
        }
        else {
            $('#panel').show();
            $('#mode-selector').show();
            $('#back').show();
            $('#panel-error').hide();
            //$('.locate-content-container').hide();
            directionsRenderer.setMap(map);
        }

        if (isClose) {
            $('.locate-content-container').show();
            $('#mode-selector').hide();
            $('#back').hide();
            $('#panel-error').hide();
            directionsRenderer.setMap(null);
            resetTravelModeSelection();
        }
    }

    function formatDataHours(operatingHours) {
        let operationHour = operatingHours.trimStart().trimEnd();

        operationHour = '<p>' + operationHour
            .replace('Tuesday', '</p><p>Tuesday')
            .replace('Wednesday', '</p><p>Wednesday')
            .replace('Thursday', '</p><p>Thursday')
            .replace('Friday', '</p><p>Friday')
            .replace('Saturday', '</p><p>Saturday')
            .replace('Sunday', '</p><p>Sunday')
            .replace('Holiday', '</p><p>Holiday');

        operationHour += '</p>';

        let html = "";
        let listOperations = [];

        operationHour = operationHour.replace(new RegExp('<p>', 'g'), '');
        operationHour = operationHour.replace(new RegExp(' - ', 'g'), '-');

        const opArrs = operationHour.split(" </p>");

        const tmplst = [];

        if (opArrs.length === 1) {
            if (opArrs[0].includes("24hrs")) {
                html += "<li>" + "Open 24 Hours" + "</li>";
                return html;
            } else {
                html += "<li>" + "Open " + opArrs[0] + "</li>";
                return html;
            }
        }else if (opArrs.length === 2 && opArrs[0].includes("Monday to")) {
            html += "<li>" + "Open " + operationHour.replace(new RegExp('</p>', 'g'), ''); + "</li>";
            return html;
        }

        let currentItem = {
            Time: '',
            FirstDay: '',
            LastDay: '',
        };

        if (opArrs.length > 1) {
            opArrs.forEach(function (opEntry, index) {
                const item = opEntry.split(/\s+/);

                const weekday = item[0].replace(/<\/?[^>]+(>|$)/g, "");

                let timeday;

                if (item[1] !== undefined) {
                    timeday = item[1].replace(/<\/?[^>]+(>|$)/g, "");
                }
                else {
                    timeday = (opArrs[index - 1].split(/\s+/))[1].replace(/<\/?[^>]+(>|$)/g, "");
                }

                const itemNext = opArrs[index + 1];

                let weekdayNext = '';
                let timedayNext = '';

                if (itemNext) {
                    weekdayNext = itemNext.split(/\s+/)[0];
                    timedayNext = itemNext.split(/\s+/)[1];
                }

                if (currentItem.Time === '') {
                    currentItem = {
                        Time: timeday,
                        FirstDay: weekday,
                        LastDay: ''
                    }

                    if (timedayNext === currentItem.Time) {
                        currentItem.LastDay = ' - ' + weekdayNext;
                    }

                    else {
                        tmplst.push(currentItem);
                        currentItem = {
                            Time: '',
                            FirstDay: '',
                            LastDay: '',
                        };
                    }
                }
                else {
                    if (currentItem.Time !== timeday) {
                        currentItem = {
                            Time: timeday,
                            FirstDay: weekday,
                            LastDay: ''
                        }
                        if (timedayNext === currentItem.Time) {
                            currentItem.LastDay = '- ' + weekdayNext;
                        }
                        else {
                            tmplst.push(currentItem);
                            currentItem = {
                                Time: '',
                                FirstDay: '',
                                LastDay: '',
                            };
                        }
                    }
                    else {
                        if (timedayNext === currentItem.Time) {
                            currentItem.LastDay = ' - ' + weekdayNext;
                        }
                        else {
                            currentItem.LastDay = ' - ' + weekday;
                            tmplst.push(currentItem);
                            currentItem = {
                                Time: '',
                                FirstDay: '',
                                LastDay: '',
                            };
                        }
                    }
                }
            });
        }

        let officeDayTemp = [], officeDay = [], alongDay = [], saturday = [],
            sunday = [];
        let tmptime = '';

        var dayOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        tmplst.forEach(function (value, index) {
            if (dayOfWeek.indexOf(value.FirstDay) !== -1) {
                switch (value.LastDay) {
                    case ' - Saturday':
                        officeDayTemp.push({
                            FirstDay: value.FirstDay,
                            LastDay: ' - Friday',
                            Time: value.Time
                        });
                        saturday.push({
                            FirstDay: 'Saturday',
                            LastDay: '',
                            Time: value.Time
                        });
                        break;
                    case ' - Sunday':
                        officeDayTemp.push({
                            FirstDay: value.FirstDay,
                            LastDay: ' - Friday',
                            Time: value.Time
                        });
                        saturday.push({
                            FirstDay: 'Saturday',
                            LastDay: '',
                            Time: value.Time
                        });
                        if (tmplst[index + 1] && tmplst[index + 1].Time === value.Time) {
                            sunday.push({
                                FirstDay: 'Sunday/Holiday',
                                LastDay: '',
                                Time: value.Time
                            });
                            tmplst.pop();
                        }
                        else {
                            sunday.push({
                                FirstDay: 'Sunday',
                                LastDay: '',
                                Time: value.Time
                            });
                        }
                        break;
                    case ' - Holiday':
                        officeDayTemp.push({
                            FirstDay: value.FirstDay,
                            LastDay: ' - Friday',
                            Time: value.Time
                        });
                        saturday.push({
                            FirstDay: 'Saturday',
                            LastDay: '',
                            Time: value.Time
                        });
                        sunday.push({
                            FirstDay: 'Sunday/Holiday',
                            LastDay: '',
                            Time: value.Time
                        });
                        break;
                    case '':
                        if (value.Time === tmptime) {
                            officeDayTemp.push(value);
                        }
                        else {
                            alongDay.push(value);
                        }
                        break;
                    default:
                        officeDayTemp.push(value);
                        tmptime = value.Time;
                }
            }

            switch (value.FirstDay) {
                case 'Saturday':
                    if (value.LastDay === ' - Sunday') {
                        if (tmplst[index + 1]) {
                            if (value.Time === tmplst[index + 1].Time) {
                                tmplst[index + 1].FirstDay = 'Sunday/Holiday';

                                sunday.push(tmplst[index + 1]);
                            }
                            else {
                                sunday.push({
                                    FirstDay: 'Sunday',
                                    LastDay: '',
                                    Time: value.Time
                                }, tmplst[index + 1]);
                            }

                            tmplst.pop();
                        }

                        tmplst[index].LastDay = '';
                    }

                    saturday.push(value);

                    break;

                case 'Sunday':
                    if (tmplst[index + 1]) {
                        if (value.Time === tmplst[index + 1].Time) {
                            tmplst[index].FirstDay = 'Sunday/Holiday';
                            sunday.push(value);
                        }
                        else {
                            sunday.push(value);
                            sunday.push(tmplst[index + 1]);
                        }

                        tmplst.pop();
                    }
                    break;

                case 'Holiday':
                    sunday.push(value);
                    break;
            }
        });

        for (var i = 0; i < officeDayTemp.length; i++) {
            if (officeDayTemp[i + 1]) {
                if (officeDayTemp[i].Time === officeDayTemp[i + 1].Time) {
                    officeDay.push({
                        Time: officeDayTemp[i].Time,
                        FirstDay: officeDayTemp[i].FirstDay,
                        LastDay: ' - Friday',
                    });
                }
            }
        }

        if (officeDay.length > 0) {
            officeDay.forEach(function (value, index) {
                html += "<li>" + value.FirstDay + value.LastDay + ": " + formatTime(value.Time) + "</li>";
            });
        }
        else {
            officeDayTemp.forEach(function (value, index) {
                html += "<li>" + value.FirstDay + value.LastDay + ": " + formatTime(value.Time) + "</li>";
            })
        }

        if (alongDay.length > 0) {
            alongDay.forEach(function (value, index) {
                html += "<li>" + value.FirstDay + value.LastDay + ": " + formatTime(value.Time) + "</li>";
            })
        }

        if (saturday.length > 0) {
            saturday.forEach(function (value, index) {
                html += "<li>" + value.FirstDay + value.LastDay + ": " + formatTime(value.Time) + "</li>";
            })
        }

        if (sunday.length > 0) {
            sunday.forEach(function (value, index) {
                html += "<li>" + value.FirstDay + value.LastDay + ": " + formatTime(value.Time) + "</li>";
            })
        }

        return html;
    }

    function formatTime(time) {
        let timeFirst, timeLast, timeTemp1, timeTemp2;

        var timeString = time.split('-');

        if (timeString[1] === undefined) {
            return timeString[0];
        }

        timeTemp1 = timeString[0].split(':');
        timeTemp2 = timeString[1].split(':');

        timeFirst = formatAmpm(timeTemp1[0], timeTemp1[1]);
        timeLast = formatAmpm(timeTemp2[0], timeTemp2[1]);

        return timeFirst + '-' + timeLast;
    }

    function formatAmpm(hour, minute) {
        let h, ampm;
        hour = parseInt(hour);
        minute = parseInt(minute);

        h = hour % 12 || 12;

        ampm = (hour < 12 || hour === 24) ? "am" : "pm";

        return h + (minute && minute !== 0 ? '.' + minute : '') + ampm;
    }

    function formatDataService(str) {
        var html = "";
        var arr = str.split(",");
        if (arr.length > 1) {
            $.each(arr, function (item) {
                if (item != arr.length - 1) {
                    html += "<li>" + arr[item] + "</li>";
                }
            });
        }
        else {
            html = str;
        }

        return html;
    }

    function isUpper(str) {
        return !/[a-z]/.test(str) && /[A-Z]/.test(str);
    }

    function splitCapitalCharacter(data) {
        var result = data[0];
        for (i = 1; i < data.length; i++) {
            var char = data[i];
            var prechar = data[i - 1];

            if (!isUpper(prechar) && isUpper(char)) {
                result += " " + char;
            }
            else {
                result += char;
            }
        }

        return result;
    }

    function resetTravelModeSelection() {
        travelMode = google.maps.TravelMode.DRIVING;
        $('#mode-selector input[type=radio]').prop('checked', false);
        $('#mode-selector #changemode-driving').prop('checked', true);
    }

    jQuery(document).ready(function ($) {
        initAutocomplete();
        if (document.getElementById('map') !== null) {
            initMapData();
        }

        //remove place_id when user manually input address
        $('.map-autocomplete').each(function () {
            var targetID = $(this).attr('target-id');
            $(this).on('change', function () {
                $('#' + targetID).val('');
            });
        });
    });
    jQuery(document).ready(function() {
        jQuery('#cur-loc-icon').insertAfter('.frontend-locate-us.side-form #edit-keyword');
    });

    // Get Current Location - Google Map API
    jQuery('#cur-loc-icon').on('click', function () {
        if ("geolocation" in navigator){
            navigator.geolocation.getCurrentPosition(function(position){ 
                var currentLatitude = position.coords.latitude;
                var currentLongitude = position.coords.longitude;
                var latlng = new google.maps.LatLng(currentLatitude, currentLongitude);
                // This is making the Geocode request
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ 'latLng': latlng },  (results, status) =>{
                    if (status == google.maps.GeocoderStatus.OK) {
                        var address = (results[0].formatted_address);
                    }
                    jQuery('.frontend-locate-us.node-form .map-autocomplete').val(address);
                    jQuery('.frontend-locate-us.side-form .map-autocomplete').val(address);
                });
            });
        }
    });

    /* Get Current Location for locate us page - Google Map API */
    jQuery('#sgp_locate_us_current_location').on('click', function () {
        if ("geolocation" in navigator){
            navigator.geolocation.getCurrentPosition(function(position){ 
                var currentLatitude = position.coords.latitude;
                var currentLongitude = position.coords.longitude;
                var latlng = new google.maps.LatLng(currentLatitude, currentLongitude);
                // This is making the Geocode request
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ 'latLng': latlng },  (results, status) =>{
                    if (status == google.maps.GeocoderStatus.OK) {
                        var address = (results[0].formatted_address);
                    }
                    jQuery('.frontend-locate-us.node-form .map-autocomplete').val(address);
                    jQuery('.frontend-locate-us.side-form .map-autocomplete').val(address);
                    jQuery('#origin-input').val(address);
                });
            });
        }
    });

    // Get Direction Mobile
    jQuery('#open_app_loction').on('click', function () {
        var destinationLat = jQuery('#destination-input').attr('data-lat');
        var destinationLng = jQuery('#destination-input').attr('data-lng');
        if( (navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1)|| (navigator.platform.indexOf("iPad") != -1)){
            window.open("http://maps.apple.com/?ll="+ destinationLat + "," + destinationLng);
        }
        else
        {
            window.open("https://www.google.com/maps/dir/?api=1&travelmode=driving&layer=traffic&destination="+ destinationLat + "," + destinationLng);
        }
    });
});

