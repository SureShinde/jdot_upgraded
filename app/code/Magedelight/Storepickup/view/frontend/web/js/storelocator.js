
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

var map;
var markers = [];
var markermessages = [];
var currmarker = [];
var previnfowindow = null;
var markerlist = [];
var circle = null;
var markerimage;
var distanceunit;
var allmarkerimage;
var mymarkers = [];
var markerCluster = [];
/*var bounds;*/


/* 
 *function for add markers 
 * @param {type} data
 * @param {type} map
 * @param {type} index
 * @returns {undefined}
 */
function addMarker(data, map, index, messages1) {
    // Create the marker
/*
    this.markerClusterer = new MarkerClusterer(map, [], {
                styles: clustermarker,
                gridSize: 10,
                maxZoom: 15
            } );

    
    self.markerClusterer.clearMarkers();*/


    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(data.lat, data.lng),
        map: map,
        icon: allmarkerimage,
        storelocatorid: data.storelocatorid,
        bounds:true,
        title: data.name
    });

    mymarkers.push(marker);

    markerlist[data.storelocatorid] = marker;
    attachSecretMessage(marker, index, map, messages1)
    return marker;

}

/*  
 * function for add message to every markers
 * @param {type} marker
 * @param {type} number
 * @param {type} map
 * @returns {undefined}
 */
function attachSecretMessage(marker, number, map, messages1) {
    var message = messages1;

    var infowindow = new google.maps.InfoWindow({
        content: message[number],
        size: new google.maps.Size(50, 50)
    });
    google.maps.event.addListener(marker, 'click', function () {
        if (previnfowindow) {
            previnfowindow.close();
        }
        previnfowindow = infowindow;
        infowindow.open(map, marker);
        currmarker = marker;
    });

}


/* Map for Listpage*/
function initializeListView(markers1, messages1) {

    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;
    var centerPosition = new google.maps.LatLng(37.4419, -122.1419);

    map = new google.maps.Map(document.getElementById("map_canvas"), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableAutoPan: false,
        center: centerPosition,
        zoom: 2,
        streetViewControl: true
    });
    directionsDisplay.setMap(map);

    var markerCluster = new MarkerClusterer(map, mymarkers, {
                styles: clustermarker,
                gridSize: 10,
                maxZoom: 15
            });
    
    markerCluster.clearMarkers();
    

    /* Start logic of search place */
    var input = document.getElementById('start-location');
    var searchBox = new google.maps.places.SearchBox(input);
    // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    var input = document.getElementById('pac-input');
    var searchBox_first = new google.maps.places.SearchBox(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function () {
        /*searchBox.setBounds(map.getBounds());*/
        /*searchBox_first.setBounds(map.getBounds());*/
    });

    map.addListener('bounds_changed', function () {
        searchBox_first.setBounds(map.getBounds());
    });

    // var markers = [];

    // [START region_getplaces]
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function () {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();

        places.forEach(function (place) {
            var icon = {
                url: place.icon,
            };

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        map.fitBounds(bounds);
        calculateAndDisplayRoute(directionsService, directionsDisplay, "TRANSIT");
    });

    searchBox_first.addListener('places_changed', function () {
        var places = searchBox_first.getPlaces();

        var slidervalue = document.getElementById("circle-radius").value;
        if (places.length == 0) {
            return;
        }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();

        places.forEach(function (place) {
            var icon = {
                url: place.icon,
            };

            if (circle) {
                if (circle.getMap()) {
                    circleMarker.setMap(null);
                    circle.setMap(null);
                }
            }
            ;
            circle = new google.maps.Circle({
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                map: map,
                center: place.geometry.location,
                radius: (distanceunit == 'km') ? slidervalue * 1000 : slidervalue * 1609.34
            });
            radius = (distanceunit == 'km') ? slidervalue * 1000 : slidervalue * 1609.34
            sliderzoomvalue = 800
            circleMarker = new google.maps.Marker({
                map: map,
                icon: markerimage,
                title: place.name,
                position: place.geometry.location
            });

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });



        map.fitBounds(bounds);

        /* Set Zoom level 10 = City */        
        map.setZoom(Math.round(15 - Math.log(radius / sliderzoomvalue) / Math.LN2));

        map.addListener('idle', function () {
            updateStoreList(messages1);
        });

        /*radius_changed*/
        google.maps.event.addListener(circle, 'radius_changed', function () {
            updateStoreList(messages1);
        });
    });



    markers = markers1;
    if(markers=="")
    {
        marker = addMarker(map);
        markerCluster.addMarker(marker);
        window.markerCluster = markerCluster;
    }
        
    var widt = document.body.offsetWidth;
    document.getElementById("map_canvas").width = widt + "px";

    
    for (index in markers) {
        if (Number(index) != index)
            break;
        
        marker = addMarker(markers[index], map, index, messages1);
        markerCluster.addMarker(marker);
        window.markerCluster = markerCluster;

    }


    var bounds = new google.maps.LatLngBounds();
    
    for (index in markers) {
        if (Number(index) != index)
            break;
        var data = markers[index];
        bounds.extend(new google.maps.LatLng(data.lat, data.lng));
    }

    // Don't zoom in too far on only one marker
    if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
        var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
        var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
        bounds.extend(extendPoint1);
        bounds.extend(extendPoint2);
    }


    map.fitBounds(bounds);

    /* attach click event to button */

    jQuery("#submit-direction").click(function () {
        calculateAndDisplayRoute(directionsService, directionsDisplay, "TRANSIT");
    });

    /* end attach click event to button */
    /* attach click event to transport mode */

    jQuery(".vertical li").click(function () {
        jQuery(this).parent().children('li').removeClass('active');
        jQuery(this).addClass('active');
        var tranmode = jQuery(this).attr("mvalue");
        calculateAndDisplayRoute(directionsService, directionsDisplay, tranmode);
    });


    /* end attach click event to transport mode */
}

/* function for set current location map */
function getCurrentLocationMap()
{
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            map.setCenter(pos);
            updateStoreList(markermessages);
        });
    }
}

function updateStoreList(messages1)
{

    /* code for getting updated list of stores */
    html = "";
    var storecount = 0;

    for (var i = markers.length, bounds = circle.getBounds(); i--; )
    {
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(markers[i].lat, markers[i].lng),
            map: map,
            icon: allmarkerimage,
            title: markers[i].name
        });
        attachSecretMessage(marker, i, map, messages1)
        google.maps.Circle.prototype.contains = function (latLng) {
            return this.getBounds().contains(latLng) && google.maps.geometry.spherical.computeDistanceBetween(this.getCenter(), latLng) <= this.getRadius();
        }
        if (!circle.contains(marker.getPosition())) {
            jQuery('li[index="' + markers[i].storelocatorid + '"]').hide();
        } else {
            jQuery('li[index="' + markers[i].storelocatorid + '"]').show();
            storecount++;
        }
    }

    if (storecount == 0)
    {
        jQuery('li[index="0"]').show();
    } else {
        jQuery('li[index="0"]').hide();
    }
    jQuery('#store-counter').text(storecount + ' Stores');
}

function updateRadius(radiusval) {
    var radius = (distanceunit == 'km') ? parseInt(radiusval) * 1000 : parseInt(radiusval) * 1609.34;
    circle.setRadius(radius);
}


/* start code for storeview page */
function initializeview(markers1, messages1) {
    var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;
    var map = new google.maps.Map(document.getElementById("map_canvas"), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableAutoPan: false,
        streetViewControl: true
    });
    directionsDisplay.setMap(map);

    /* Start logic of search place */
    var input = document.getElementById('start-location');
    var searchBox = new google.maps.places.SearchBox(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function () {
        searchBox.setBounds(map.getBounds());
        /*searchBox_first.setBounds(map.getBounds());*/
    });

    // var markers = [];
    var markers = [];
    // [START region_getplaces]
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function () {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();

        places.forEach(function (place) {
            var icon = {
                url: place.icon,
            };

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        map.fitBounds(bounds);
        calculateAndDisplayRoute(directionsService, directionsDisplay, "TRANSIT");
    });


    var markers = markers1;

    var widt = document.body.offsetWidth;
    document.getElementById("map_canvas").width = widt + "px";


    for (index in markers) {
        if (Number(index) != index)
            break;
        addMarker(markers[index], map, index, messages1);
    }
    var bounds = new google.maps.LatLngBounds();
    for (index in markers) {
        if (Number(index) != index)
            break;
        var data = markers[index];
        bounds.extend(new google.maps.LatLng(data.lat, data.lng));
    }

    // Don't zoom in too far on only one marker
    if (bounds.getNorthEast().equals(bounds.getSouthWest())) {
        var extendPoint1 = new google.maps.LatLng(bounds.getNorthEast().lat() + 0.01, bounds.getNorthEast().lng() + 0.01);
        var extendPoint2 = new google.maps.LatLng(bounds.getNorthEast().lat() - 0.01, bounds.getNorthEast().lng() - 0.01);
        bounds.extend(extendPoint1);
        bounds.extend(extendPoint2);
    }

    map.fitBounds(bounds);

    /* attach click event to button */

    jQuery("#submit-direction").click(function () {
        calculateAndDisplayRoute(directionsService, directionsDisplay, "TRANSIT");
    });

    /* end attach click event to button */
    /* attach click event to transport mode */

    jQuery(".vertical li").click(function () {
        jQuery(this).parent().children('li').removeClass('active');
        jQuery(this).addClass('active');
        var tranmode = jQuery(this).attr("mvalue");
        calculateAndDisplayRoute(directionsService, directionsDisplay, tranmode);
    });

    /* end attach click event to transport mode */

}
function getCurrentLocation(e)
{   
    e.preventDefault();
    if (navigator.geolocation) {

        navigator.geolocation.getCurrentPosition(function (position) {
            var geocoder = new google.maps.Geocoder();
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            geocoder.geocode({'latLng': latlng}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        jQuery('#start-location').val(results[1].formatted_address);
                    }
                    else{
                        alert('Location not found');
                    }
                }
                    else{
                        alert('Location not found');
                    }
            });
        });
    }
}
function calculateAndDisplayRoute(directionsService, directionsDisplay, travelmode) {

    /* validate direction form */
    require([
        'jquery', // jquery Library
        'jquery/ui', // Jquery UI Library
        'jquery/validate', // Jquery Validation Library
        'mage/translate' // Magento text translate (Validation message translte as per language)
    ], function ($) {
        if ($('#get-direction').valid()) {

            directionsDisplay.setPanel(document.getElementById('directions-panel'));

            /* get dirction between to places */
            directionsService.route({
                origin: jQuery('#start-location').val(),
                destination: jQuery('#end-location').val(),
                travelMode: travelmode,
                unitSystem: (distanceunit == 'km') ? google.maps.UnitSystem.METRIC : google.maps.UnitSystem.IMPERIAL
            }, function (response, status) {
                if (status === google.maps.DirectionsStatus.OK) {
//                                    directionsService.units('imperial');
                    directionsDisplay.setDirections(response);

                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
            return false;
            /* end get dirction between to places */

        }

    });
}


/* start logic for shipping map */
/* function for intialized storelist page */

function initializeshipMap(markers1, messages1) {

    map = new google.maps.Map(document.getElementById("map_canvas"), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableAutoPan: false,
        zoom: 4,
        streetViewControl: false
    });

    markermessages = messages1;
    markers = markers1;
    /* Start logic of search place */
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);
    //map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function () {
        searchBox.setBounds(map.getBounds());
    });

    // var markers = [];

    // [START region_getplaces]
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function () {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();

        places.forEach(function (place) {
            var icon = {
                url: place.icon,
            };

            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });

        map.fitBounds(bounds);
        updateStoreList(messages1);
    });

    var widt = document.body.offsetWidth;
    document.getElementById("map_canvas").width = widt + "px";

    for (index in markers) {
        if (Number(index) != index)
            break;
        addMarker(markers[index], map, index, messages1);
    }
    var bounds = new google.maps.LatLngBounds();
    for (index in markers) {
        if (Number(index) != index)
            break;
        var data = markers[index];
        bounds.extend(new google.maps.LatLng(data.lat, data.lng));
    }

    map.fitBounds(bounds);
    jQuery("#light").append(jQuery("#map-container").html());

}
/* fuction for open map in popup */
function visibleMap()
{

    jQuery("#light").removeClass("white_content_hidden");
    jQuery("#light").addClass("white_content");
    jQuery("#fade").removeClass("black_overlay_hidden");
    jQuery("#fade").addClass("black_overlay");
    /*jQuery('input#pac-input').appendTo('.shipping-map-header-container');*/
}

/* fuction for close map from popup */
function disableMap()
{
    initializeshipMap(markers1, messages1);
    jQuery('#pac-input').val('');
    jQuery("#light").removeClass("white_content");
    jQuery("#light").addClass("white_content_hidden");
    jQuery("#fade").removeClass("black_overlay");
    jQuery("#fade").addClass("black_overlay_hidden");
}

/* apply store to shipping method */
function applyStore()
{
    if (!jQuery.isEmptyObject(currmarker))
    {
        var selectstoreId = currmarker.storelocatorid;
        jQuery("#pickup-store").val(selectstoreId).change();
        disableMap();
    }
}
/* function which called when store value changed from select option*/
function changeStore(ref)
{
    var currentStoreId = jQuery(ref).val();
    google.maps.event.trigger(markerlist[currentStoreId], 'click');

}
function datechanged(ref, url)
{
    var currstoreid = jQuery("#pickup-store").val();
    var pickupdate = jQuery(ref).val();
    jQuery.ajax({
        url: url,
        dataType: 'json',
        data: 'curstoreid=' + currstoreid + '&date=' + pickupdate,
        success: function (data) {
            if (data.success)
            {
                console.log("helo");
            } else
            {
                location.reload();
            }
        }
    });
}
/* end logic for shipping map */

/* call on streetview link to find street view */

function streetview(lat, lon) {
    var latitude = Number(lat);
    var longitude = Number(lon);
    var fenway = {lat: latitude, lng: longitude};

    var sv = new google.maps.StreetViewService();
    panorama = map.getStreetView();
    sv.getPanorama({location: fenway, radius: 50}, processSVData);

}

/* Check street view Avauilabel or not */
function processSVData(data, status) {
    if (status === google.maps.StreetViewStatus.OK) {

        panorama.setPano(data.location.pano);
        panorama.setPov({
            heading: 270,
            pitch: 0
        });
        panorama.setVisible(true);

    } else {
        alert('Street View data not found for this location.');
    }
}