/*
MegaZine 3 - A Flash application for easy creation of book-like webpages.
Copyright (C) 2007-2008 Florian Nuecke

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses/.
*/

/**
 * Interface for calling functions in the MegaZine engine. For more info see API of JSConnector.
 *
 * Usage: onxxx="javascript:MegaZine.yyy(zzz);", where xxx is the event type, yyy is the function
 * to call, and zzz are possible parameters. Example: onclick="javascript:MegaZine.nextPage();"
 */
MegaZine = {
	// Name of the movie (flash object). This should be the value of the id set in the embedSWF call.
	moviename : "megazine",
	
	/*
	 * Adjust the following functions to handle events.
	 */
	
	// Called when the automatic page turning (slideshow) is started.
	onSlideStart : function() {
	},
	
	// Called when the automatic page turning (slideshow) is stopped.
	onSlideStop : function() {
	},
	
	// Called when the current page changes. page will always be an even number.
	onPageChange : function(page) {
	},
	
	// Called when sounds should be muted.
	onMute : function() {
	},
	
	// Called when sounds are no longer muted.
	onUnmute : function() {
	},
	
	// Called when the MegaZine instance's status changes.
	onStatusChange : function(state, prevstate) {
	},
	
	// Called when the MegaZine instance's flip status changes (pages moving or not).
	onFlipStatusChange : function(state, prevstate) {
	},
	
	// Called when the displayed image in zoom mode changes (in galleries).
	// page is the number of the page containing the now displayed image.
	onZoomChanged : function(page) {
	},
	
	// Called when the MegaZine instance's zoom status changes (zoom opens or closes).
	// page is the number of the page containing the now/last displayed image.
	onZoomStatusChange : function(state, prevstate, page) {
	},
	
	
	/*
	 * !!! Do not change the following functions, just call them !!!
	 */
	
	/* This utility function resolves the string movie to a Flash object reference based on browser type. */
	getMovie : function() { return (navigator.appName.indexOf("Microsoft") != -1) ? window[MegaZine.moviename] : document[MegaZine.moviename]; },
	
	// Returns current anchor. Can be null.
	getCurrentAnchor   : function() { return MegaZine.getMovie().getCurrentAnchor(); },
	// Returns current page number (always an even number).
	getCurrentPage     : function() { return MegaZine.getMovie().getCurrentPage(); },
	// Return if mouse interaction with pages is enabled.
	isDraggingEnabled  : function() { return MegaZine.getMovie().isDraggingEnabled(); },
	// Set whether mouse interaction with pages is enabled.
	setDraggingEnabled : function(enable) { MegaZine.getMovie().setDraggingEnabled(enable); },
	// Return if sounds are muted.
	isMuted            : function() { return MegaZine.getMovie().isMuted(); },
	// Set muted state for sounds.
	setMuted           : function(mute) { MegaZine.getMovie().setMuted(mute); },
	// Returns number of pages in the book.
	getPageCount       : function() { return MegaZine.getMovie().getPageCount(); },
	// Returns page height.
	getPageHeight      : function() { return MegaZine.getMovie().getPageHeight(); },
	// Return page width.
	getPageWidth       : function() { return MegaZine.getMovie().getPageWidth(); },
	// Return state (loading, ready).
	getStatus          : function() { return MegaZine.getMovie().getStatus(); },
	// Return flip state (page moving or not).
	getFlipStatus      : function() { return MegaZine.getMovie().getFlipStatus(); },
	// Return zoom state (open, closed).
	getZoomStatus      : function() { return MegaZine.getMovie().getZoomStatus(); },
	// Returns if reflections are enabled.
	hasReflection      : function() { return MegaZine.getMovie().hasReflection(); },
	// Sets reflection usage.
	setReflection      : function(enabled) { MegaZine.getMovie().setReflection(enabled); },
	// Returns whether shadows are enabled.
	hasShadows         : function() { return MegaZine.getMovie().hasShadows(); },
	// Sets shadow usage.
	setShadows         : function(enabled) { MegaZine.getMovie().setShadows(enabled); },
	// Navigate to an anchor in the book.
	gotoAnchor         : function(id, instant) { if (instant == null) instant = false; MegaZine.getMovie().gotoAnchor(id, instant); },
	// Navigate to a page in the book.
	gotoPage           : function(page, instant) { if (instant == null) instant = false; MegaZine.getMovie().gotoPage(page, instant); },
	// Navigate to the first page in the book.
	firstPage          : function(instant) { if (instant == null) instant = false; MegaZine.getMovie().firstPage(instant); },
	// Navigate to the last page in the book.
	lastPage           : function(instant) { if (instant == null) instant = false; MegaZine.getMovie().lastPage(instant); },
	// Navigate to the next page.
	nextPage           : function(instant) { if (instant == null) instant = false; MegaZine.getMovie().nextPage(instant); },
	// Navigate to the previous page.
	prevPage           : function(instant) { if (instant == null) instant = false; MegaZine.getMovie().prevPage(instant); },
	// Start slideshow / automatic page turning.
	slideStart         : function() { MegaZine.getMovie().slideStart(); },
	// Stop slideshow / automatic page turning.
	slideStop          : function() { MegaZine.getMovie().slideStop(); },
	// Zoom in if zoom is open (else returns false).
	zoomIn             : function() { return MegaZine.getMovie().zoomIn(); },
	// Zoom out if zoom is open (else returns false).
	zoomOut            : function() { return MegaZine.getMovie().zoomOut(); },
	// Opens zoom.
	openZoom           : function(galleryOrPath, page, number) { if (page == null) page = -1; if (number == null) number = 0; MegaZine.getMovie().openZoom(galleryOrPath, page, number); }
};
