var melisHelper = (function(){

    var version = "2.0.0";

    // CACHE SELECTORS
    var $body = $("body");
    var $navTabs = $("#melis-id-nav-bar-tabs");

    function melistranslators(transKey){
        var translated = translators[transKey]
        if(translated === undefined){
            translated = transKey;
        }
        return translated;
    }

    // OK NOTIFICATION
    function melisOkNotification(title, message, color){
        if(!color) color = "#72af46";
        $.gritter.add({
            title: melistranslators(title),
            text: melistranslators(message),
            time: 3000,
            image: '/melis/MelisCore/MelisAuth/getProfilePicture?v=' + new Date().getTime(),
        });
        //set the color
        $(".gritter-item").css("background",color);
    }

    // KO NOTIFICATION
    function melisKoNotification(title, message, errors, closeByButtonOnly){
        if(!closeByButtonOnly) closeByButtonOnly = "closeByButtonOnly";
        ( closeByButtonOnly !== 'closeByButtonOnly' ) ? closeByButtonOnly = 'overlay-hideonclick' : closeByButtonOnly = '';

        var errorTexts = '<h3>'+ melistranslators(title) +'</h3>';

        errorTexts +='<h4>'+ melistranslators(message) +'</h4>';
        $.each( errors, function( key, error ) {
            if(key !== 'label'){
                errorTexts += '<p class="modal-error-cont"><b>'+ (( errors[key]['label'] == undefined ) ? ((errors['label']== undefined) ? key : errors['label'] ) : errors[key]['label'] )+ ': </b>  ';

                // catch error level of object
                try {
                    $.each( error, function( key, value ) {
                        if(key !== 'label'){
                            errorTexts += '<span><i class="fa fa-circle"></i>'+ value + '</span>';
                        }
                    });
                } catch(Tryerror) {
                    if(key !== 'label'){
                        errorTexts +=  '<span><i class="fa fa-circle"></i>'+ error + '</span>';
                    }
                }
                errorTexts += '</p>';
            }
        });

        var div = "<div class='melis-modaloverlay "+ closeByButtonOnly +"'></div>";
        div += "<div class='melis-modal-cont KOnotif'>  <div class='modal-content'>"+ errorTexts +" <span class='btn btn-block btn-primary'>"+ translators.tr_meliscore_notification_modal_Close +"</span></div> </div>";
        $body.append(div);
    }
    /**
     * KO NOTIFICATION for Multiple Form
     */
    function melisMultiKoNotification(title, message, errors, closeByButtonOnly){
        if(!closeByButtonOnly) closeByButtonOnly = true;
        var closeByButtonOnly = ( closeByButtonOnly !== true ) ?  'overlay-hideonclick' : '';

        var errorTexts = '<h3>'+ melisHelper.melistranslators(title) +'</h3>';
        errorTexts +='<h4>'+ melisHelper.melistranslators(message) +'</h4>';

        $.each( errors, function( key, error ) {
            if(key !== 'label'){
                errorTexts += '<p class="modal-error-cont"><b>'+ (( errors[key]['label'] == undefined ) ? ((errors['label']== undefined) ? key : errors['label'] ) : errors[key]['label'] )+ ': </b>  ';
                // catch error level of object
                try {
                    $.each( error, function( key, value ) {
                        if(key !== 'label' && key !== 'form'){

                            $errMsg = '';
                            if(value instanceof Object){
                                $errMsg = value[0];
                            }else{
                                $errMsg = value;
                            }
                            errorTexts += '<span><i class="fa fa-circle"></i>'+ $errMsg + '</span>';
                        }
                    });
                } catch(Tryerror) {
                    if(key !== 'label' && key !== 'form'){
                        errorTexts +=  '<span><i class="fa fa-circle"></i>'+ error + '</span>';
                    }
                }
                errorTexts += '</p>';
            }
        });

        var div = "<div class='melis-modaloverlay "+ closeByButtonOnly +"'></div>";
        div += "<div class='melis-modal-cont KOnotif'>  <div class='modal-content'>"+ errorTexts +" <span class='btn btn-block btn-primary'>"+ translators.tr_meliscore_notification_modal_Close +"</span></div> </div>";
        $body.append(div);
    }

    /**
     * This method will Highlight an input label where an error occured
     * @param success, 1 or 0
     * @param errors, Object array
     * @param selector, element selector
     */
    function highlightMultiErrors(success, errors, selector){
        if(!selector) selector = activeTabId;
        // remove red color for correctly inputted fields
        $("" + selector + " .form-group label").css("color", "inherit");
        // if all form fields are error color them red
        if(success === 0){
            $.each( errors, function( key, error ) {
                if("form" in error){
                    $.each(this.form, function( fkey, fvalue ){
                        $("#" + fvalue + " .form-control[name='"+key +"']").prev("label").css("color","red");
                    });
                }
            });
        }
    }

    /**
     * This method will initialize a element to DataRangePicker plugin
     * @param selector, element selector
     * @param callBackFunction, callback function of the date range picker
     */
    function initDateRangePicker(selector, callBackFunction){
        setTimeout(function(){
            var target = $(selector);
            target.addClass("dt-date-range-picker");
            target.html(''+translators.tr_meliscore_datepicker_select_date+' <i class="glyphicon glyphicon-calendar fa fa-calendar"></i> <span></span> <b class="caret"></b>');
            var sToday = translators.tr_meliscore_datepicker_today;
            var sYesterday = translators.tr_meliscore_datepicker_yesterday;
            var sLast7Days = translators.tr_meliscore_datepicker_last_7_days;
            var sLast30Days = translators.tr_meliscore_datepicker_last_30_days;
            var sThisMonth = translators.tr_meliscore_datepicker_this_month;
            var sLastMonth = translators.tr_meliscore_datepicker_last_month;

            var rangeStringParam = {};
            rangeStringParam[sToday] = [moment(), moment()];
            rangeStringParam[sYesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            rangeStringParam[sLast7Days] = [moment().subtract(6, 'days'), moment()];
            rangeStringParam[sLast30Days] = [moment().subtract(29, 'days'), moment()];
            rangeStringParam[sThisMonth] = [moment().startOf('month'), moment().endOf('month')];
            rangeStringParam[sLastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

            target.daterangepicker({
                startDate: moment().subtract(10, 'years'),
                endDate: moment(),
                locale : {
                    format: melisDateFormat,
                    applyLabel: translators.tr_meliscore_datepicker_apply,
                    cancelLabel: translators.tr_meliscore_datepicker_cancel,
                    customRangeLabel: translators.tr_meliscore_datepicker_custom_range,
                },
                ranges: rangeStringParam
            }, function(start, end) {
                target.find("span").html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            });

            target.on('apply.daterangepicker', function(ev, picker){
                if(callBackFunction !== undefined){
                    callBackFunction(ev, picker);
                }
            });
        }, 1000);
    }

    function initSwitch(selector){
        var targetInput = $(selector);
        if(targetInput.length){
            var parentDiv = targetInput.parent("div.form-group");
            var attribRequired = '';
            if(typeof targetInput.data("required") != 'undefined'){
                attribRequired = ' *';
            }
            var attribTooltip = '';
            if(typeof targetInput.data("tooltip") != 'undefined'){
                attribTooltip = '<i class="fa fa-info-circle fa-lg" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="' + targetInput.data("tooltip") +'"></i>';
            }
            var switchBtn = '<label for="'+targetInput.attr("name")+'">'+targetInput.data("label") + attribRequired + attribTooltip+'</label>'
                +'<div class="make-switch user-admin-switch" data-label-icon="glyphicon glyphicon-resize-horizontal" data-on-label="'+translators.tr_meliscore_common_yes+'" data-off-label="'+translators.tr_meliscore_common_no+'" style="display: block;">'
                +'<input type="checkbox" name="'+targetInput.attr("name")+'" id="'+targetInput.attr("name")+'">'
                +'</div>';
            parentDiv.html(switchBtn);
            $('.user-admin-switch').bootstrapSwitch('destroy', true);
            $('.user-admin-switch').bootstrapSwitch();
        }else{
            console.log("Selector not found");
        }

    }

    // SWITCH ACTIVE TABS =============================================================================================================
    function tabSwitch(tabID){
        // update new activeTabId
        activeTabId = tabID;

        // run and check all the <li> to remove the 'active class'
        $("body #melis-id-nav-bar-tabs li, .container-level-a").each(function(){
            $(this).removeClass('active');
        });

        $("body #melis-id-nav-bar-tabs li").each(function(){
            $(this).removeClass('active-parent on');
        });

        var subMenu = $("#melis-id-nav-bar-tabs a.tab-element[data-id='"+ tabID +"']").closest(".nav-group-dropdown");
        if(subMenu.length) {
            $(subMenu).parents("li").addClass("active-parent on");
        }

        //add active class to the parent of the cliked <a> ( to the <li> )
        $("#melis-id-nav-bar-tabs a.tab-element[data-id='"+ tabID +"']").closest("li").addClass("active");
        $("#melis-id-nav-bar-tabs a.tab-element[data-id='"+ tabID +"']").parent("li").parents("li").addClass("active-parent on");

        //show current selected container
        $("#" + tabID).addClass("active");
    }

    // CLOSE TAB AND REMOVE ===========================================================================================================
    function tabClose(ID){
        var tabContentID =  (typeof ID === 'string') ? ID :  $(this).data("id");
        var currentParent = $(".tabsbar a[data-id='"+tabContentID+"']").parent("li");
        var nextActiveTab = currentParent.next("li").children().data("id");
        var prevActiveTab = currentParent.prev("li").children().data("id");
        var tabCount = $navTabs.children("li").length;
        var removedWidth = currentParent.width();
        var currentGrandParent = currentParent.parent().parent("li").find(".tab-element").data("id");

        var navBox = currentParent.closest(".scroll");
        var hasdropdown = $(navBox).find(".nav-group-dropdown");
        if(currentParent.hasClass("active-parent")) {
            var tabMenuGroup = currentParent.find(".nav-group-dropdown");
            tabMenuGroup.find("li a.tab-element").each(function() {
                var tabeMenuDataID = $(this).data("id");
                // remove the tabs corresponds to tab menu
                $("#"+tabeMenuDataID).remove();
            });

            // when close tab menu switch the tab active
            if(tabCount >= 1) {
                if(activeTabId === tabContentID) {
                    tabSwitch(nextActiveTab);
                } else {
                    tabSwitch(prevActiveTab);
                }
            }
        }

        if( currentParent.index() === 0 ){
            if(currentParent.siblings().length === 0) {
                currentParent.parent(".nav-group-dropdown").remove();
            }
            currentParent.remove();
            $("#"+tabContentID).remove();

            if( tabCount >= 1){
                if(activeTabId === tabContentID){
                    // switch to grand parent li tab
                    tabSwitch(currentGrandParent);
                }
            }
        }
        else{
            currentParent.remove();
            $("#"+tabContentID).remove();

            if( tabCount >= 1){
                if(activeTabId === tabContentID){
                    tabSwitch(prevActiveTab);
                }
            }
        }

        // check scroll class exists
        if(navBox) {
            // check if menu are too many
            if($(navBox).height() < 400) {
                $(navBox).removeClass("scroll");
            }
        }

        // get the <ul> container width and disable the tabExpander
        var navUlContainer = 1;
        $('#id_meliscore_header #melis-id-nav-bar-tabs > li').each(function() {
            navUlContainer += $(this).outerWidth();
        });

        if( navUlContainer < $("#melis-navtabs-container-inner").width() ){
            tabExpander.checkTE();
        }
        else{
            var leftOffset = $navTabs.position().left;

            if( leftOffset === -1 ) {}
            else if( leftOffset !== 0 ){
                $("#melis-id-nav-bar-tabs").animate({
                    left: (leftOffset + removedWidth)
                }, 0);
            }
        }
        checkSubMenu();

        // [ Mobile ] when closing a page
        if( melisCore.screenSize <= 767 ){
            $("#res-page-cont").trigger("click");

            // check if there are no contents open
            if( $navTabs.children("li").length === 0){
                var empty = '<b>(' + translators.tr_meliscore_empty +')</b>';
                $("#res-page-cont span").append(empty);
            }
        }

        // dataTable responsive plugin ----=[ PLUGIN BUG FIX ]=-----
        $("table.dataTable").DataTable().columns.adjust().responsive.recalc();
    }

    // TAB OPEN =====================================================================================================================
    function tabOpen(title, icon, zoneId, melisKey, parameters, navTabsGroup, callback) {
        //check if the tab is already open and added to the main nav
        var alreadyOpen = $("body #melis-id-nav-bar-tabs li a.tab-element[data-id='"+ zoneId +"']");

        if( alreadyOpen.length < 1 ){
            var li = "<li data-tool-name='"+ title +"' data-tool-icon='"+ icon +"' data-tool-id='"+ zoneId +"' data-tool-meliskey='"+melisKey+"'>";
            li += "<a data-bs-toggle='tab' class='dropdown-toggle menu-icon tab-element' href='#"+ zoneId + "' data-id='" + zoneId + "' title='"+ title +"'>";
            li += "<i class='fa "+ icon +" fa-2x'></i><span class='navtab-pagename'>";
            li += title + "</span></a>";
            li += "<a class='close close-tab' data-id='" + zoneId + "'>"+ translators.tr_meliscore_notification_modal_Close +"</a>";
            li += "</li>";

            // check if it has parent nav
            if(navTabsGroup && navTabsGroup !== "undefined") {
                var navParentGroup = $(".tab-element[data-id='"+navTabsGroup+"']");

                if( $(navParentGroup).length > 0 ) {
                    // find parent nav li
                    var navBox = navParentGroup.closest("li");

                    // find nav-group-dropdown that has the id of navTabsGroup
                    var hasdropdown = $(navBox).find(".nav-group-dropdown");



                    if($(hasdropdown).length) {
                        // check if menu are too many
                        if($(hasdropdown).first().height() > 350) {
                            $(hasdropdown).first().addClass("scroll");
                        }

                        // add li in the first nav-group-dropdown
                        $(hasdropdown).first().append(li);

                    } else {
                        // create a sub menu ul and append the li
                        navBox.append("<ul class='nav-group-dropdown'></ul>");
                        navBox.find(".nav-group-dropdown").append(li);
                    }
                } else {
                    $("body #melis-id-nav-bar-tabs").append(li);
					/*                    if(navTabsGroup == "design_module") {
					 var liTest = "<li>";
					 liTest += "<a data-toggle='tab' class='dropdown-toggle menu-icon tab-element' href='#"+ zoneId + "' data-id='design_module'>";
					 liTest += "<i class='fa  fa-paint-brush fa-2x'></i><span class='navtab-pagename'>";
					 liTest += 'Design '+ title + "</span></a>";
					 liTest += "<a class='close close-tab' data-id='" + zoneId + "'>"+ translators.tr_meliscore_notification_modal_Close +"</a>";
					 liTest += "</li>";
					 $("body #melis-id-nav-bar-tabs").append(liTest);
					 } else {
					 // append the <li> to the menu
					 $("body #melis-id-nav-bar-tabs").append(li);
					 }*/

                }
            } else {
                // append the <li> to the menu
                $("body #melis-id-nav-bar-tabs").append(li);
            }

            checkSubMenu();

            // [ Mobile ] when opening a page
            if( melisCore.screenSize <= 767 ){
                // check if there are no contents open
                if( $navTabs.children("li").length > 0){
                    $("#res-page-cont span b").remove();
                }

                // close sidebar after opening a page from it
                $body.removeClass('sidebar-mini');
                $("#id_meliscore_leftmenu, #id_meliscore_footer").removeAttr('style');

                // slide up the dropdown menu
                $("#melis-id-nav-bar-tabs").slideUp(300);
                $("#res-page-cont i").removeClass("move-arrow");
            }

            var div = "<div data-meliskey='" + melisKey + "' id='" + zoneId + "' class='tab-pane container-level-a'></div>";
            $('#melis-id-body-content-load').append(div);

            //set active tab ID
            activeTabId = zoneId;

            //make the new tab active
            tabSwitch(zoneId);

            //load the page content
            var fnCallback = null;

            if ( callback !== undefined || callback !== null) {
                fnCallback = callback;
            }
            zoneReload(zoneId, melisKey, parameters, fnCallback);

            // check if tabExpander(); needs to be activated or not
            tabExpander.checkTE();

            //focus the newly opened tab if tabExpander() is enabled
            if( tabExpander.checkStatus() === 'enabled' ){
                if(typeof navTabsGroup == "undefined") {
                    $(".melis-tabnext").trigger("click");
                }
            }

        }
        else{
            //make the new tab and content active instead of realoading
            tabSwitch(zoneId);
        }
    }

    // CHECK SUBMENU =================================================================================
    function checkSubMenu() {
        $("body #melis-id-nav-bar-tabs li").each(function(){
            if($(this).children("ul").length){
                $(this).addClass("has-sub");
            } else {
                $(this).removeClass("has-sub");
            }
        });
    }

    // EXECUTE CALLBACK FUNCTIONS FROM ZONE RELOADING =================================================================================
    function executeCallbackFunction(functionName, context) {
        var namespaces = functionName.split(".");
        var func = namespaces.pop();
        for(var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }

        // check the validity of the JS callback function
        if( context[func] !== undefined){
            return context[func].apply(context);
        }
    }

    // ZONE RELOADING =================================================================================================================
    function zoneReload(zoneId, melisKey, parameters, callback){

        var datastring = { cpath: melisKey };

        //add parameters value to datastring object if available
        if ( parameters !== undefined ) {
            $.each(parameters, function( index, value ) {
                datastring[index] = value;
            });
        }

        // add the temp loader
        var tempLoader = '<div id="loader" class="overlay-loader"><img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12"></div>';
        $("#"+zoneId).append(tempLoader);

        $.ajax({
            url         : '/melis/zoneview',
            data        : datastring,
            encode		: true,
            dataType	: "json"
        }).done(function(data, status, xhr){

            // hide the loader
            $('.loader-icon').removeClass('spinning-cog').addClass('shrinking-cog');

            setTimeout(function() {
                if( data !== null ){
                    $("#"+zoneId).html(data.html).children().unwrap();

                    // set the current active tab based from 'activeTabId' value
                    tabSwitch(activeTabId);

                    // set active the the 'Edition' tab and its 'Tab Content'
                    $("#" + zoneId + " .nav-tabs li:first-child").addClass("active");
                    $("#" + zoneId + " .tab-content > div:first-child").addClass("active");

                    // --------------------------------------------------------------
                    // Run callback scripts here | from app.interface
                    // --------------------------------------------------------------
                    var jsCallbacks = data.jsCallbacks;

                    $.each( jsCallbacks, function( key, value ) {

                        // check if there is more than 1 function in a single jsCallback from app.interface
                        // example: 'jscallback' => 'simpleChartInit(); anotherFunction();'  separated by (space)
                        var splitFunctions = value.split(" ");

                        if( splitFunctions.length > 1){
                            // run all the function extracted from a single jsCallback
                            $.each( splitFunctions, function( key, value ) {
                                value = value.slice(0, -3);
                                executeCallbackFunction(value, window);
                            });
                        }
                        else{
                            value = value.slice(0, -3);
                            executeCallbackFunction(value, window);
                        }
                    });
                }
                else{
                    $('#melis-id-nav-bar-tabs a[data-id="' + zoneId + '"]').parent("li").remove();
                    $('#'+zoneId).remove();

                    melisHelper.melisKoNotification( "Error Fetching data", "No result was retreived while doing this operation.", "no error datas returned", '#000' );
                }
                if ( callback !== undefined || callback !== null) {
                    if (callback) {
                        callback();
                    }
                }
            }, 300);
        }).fail(function(xhr, textStatus, errorThrown){

            alert( translators.tr_meliscore_error_message );

            //hide the loader
            $('.loader-icon').removeClass('spinning-cog').addClass('shrinking-cog');
            $('#melis-id-nav-bar-tabs a[data-id="' + zoneId + '"]').parent("li").remove();
            $('#'+zoneId).remove();

        });
    }

    // Requesting flag set to false so this function will set state to ready
    var createModalRequestingFlag = false;
    // CREATE MODAL =================================================================================================================
    function createModal(zoneId, melisKey, hasCloseBtn, parameters, modalUrl, callback, modalBackDrop){
        // declaring parameters variable for old / cross browser compatability
        if (typeof(modalUrl)==='undefined') modalUrl = null;
        if (typeof(callback)==='undefined') callback = null;
        if (typeof(modalBackDrop)==='undefined') modalBackDrop = true;

        if (createModalRequestingFlag == false){

            // Requesting flag set to true so this function will execute any action while still requesting
            createModalRequestingFlag = true;

            // if no modalUrl is supplied it will use the default modal layout from melisCore
            if(modalUrl === undefined || modalUrl == null){
                modalUrl = '/melis/MelisCore/MelisGenericModal/genericModal';
            }

            var datastring = {
                id : zoneId,
                melisKey : melisKey,
                hasCloseBtn : hasCloseBtn,
                parameters: parameters,
            };

            $.ajax({
                url         : modalUrl,
                data        : datastring,
                encode		: true
            }).done(function(data){
                // Requesting flag set to false so this function will set state to ready
                createModalRequestingFlag = false;

                $("#melis-modals-container").append(data);
                var modalID = $(data).find(".modal").attr("id");
                melisHelper.zoneReload(zoneId, melisKey, parameters);

                $("#" + modalID).modal({
                    show: true,
                    keyboard : false,
                    backdrop : modalBackDrop
                });

                if(typeof callback !== "undefined" && typeof callback === "function") {
                    callback();
                }

            }).fail(function(xhr, textStatus, errorThrown){
                alert("ERROR !! Status = "+ textStatus + "\n Error = "+ errorThrown + "\n xhr = "+ xhr.statusText);
                // Requesting flag set to false so this function will set state to ready
                createModalRequestingFlag = true;
            });
        }
    }

    // Stating zone to loading
    function loadingZone(targetElem){
        if(targetElem.length){
            var tempLoader = '<div id="loadingZone" class="overlay-loader"><img class="loader-icon spinning-cog" src="/MelisCore/assets/images/cog12.svg" data-cog="cog12"></div>';
            targetElem.attr("style", "position: relative");
            targetElem.append(tempLoader);
        }
    }

    // Removing loading state on zone
    function removeLoadingZone(targetElem){
        if(targetElem.length){
            targetElem.find("#loadingZone").remove();
        }
    }

    // disable single tab
    function disableTab(tabId) {
        $("li a.tab-element[data-id='"+tabId+"']").css('pointer-events','none').parent().css("cursor", "not-allowed");
    }

    // enable single tab
    function enableTab(tabId) {
        $("li a.tab-element[data-id='"+tabId+"']").css('pointer-events','auto').parent().css("cursor", "pointer");
    }

    // disabled all tabs
    function disableAllTabs()
    {
        $.each($("#melis-id-nav-bar-tabs li a"), function(i, v) {
            var tabId = $(v).data("id");
            disableTab(tabId);
        });

        // disable navigation too
        $.each($("ul.sideMenu"), function(i ,v) {
            $(v).css('pointer-events','none').parent().css("cursor", "not-allowed");
        });
    }

    // enables all tabs
    function enableAllTabs()
    {
        $.each($("#melis-id-nav-bar-tabs li a"), function(i, v) {
            var tabId = $(v).data("id");
            enableTab(tabId);
        });

        $.each($("ul.sideMenu"), function(i ,v) {
            $(v).css('pointer-events','none').css('pointer-events','auto').parent().css("cursor", "pointer");
        });
    }


    // BIND AND DELEGATE EVENTS =====================================================================================================

    // close tab
    $body.on("click", ".close-tab", tabClose );

    // close the KO notification
    $("body").on("click", ".melis-modal-cont.KOnotif span.btn, .overlay-hideonclick, .delete-page-modal .cancel, .melis-prompt, melis-prompt .cancel", function(){
        $(".melis-modaloverlay, .melis-modal-cont").remove();
    });

    // Input-group that has clear function button
    $body.on("click", ".meliscore-clear-input", function(){
        // Clearing the input of input-group
        $(this).closest(".input-group").find("input").val("");
    });

	/*
	 * RETURN ========================================================================================================================
	 * include your newly created functions inside the array so it will be accessable in the outside scope
	 * sample syntax in calling it outside - melisHelper.zoneReload(parameters);
	 */

    return{
        //key - access name outside									// value - name of function above

        // javascript translators function
        melistranslators									:			melistranslators,

        // notifications
        melisOkNotification 							:       	melisOkNotification,
        melisKoNotification 							: 			melisKoNotification,

        // multiple KO notifications
        melisMultiKoNotification						: 			melisMultiKoNotification,
        highlightMultiErrors							: 			highlightMultiErrors,

        // initialize dateRangePicker
        initDateRangePicker								:			initDateRangePicker,

        // initialize bootstrap switch
        initSwitch										: 			initSwitch,

        // tabs
        tabSwitch 										: 			tabSwitch,
        tabClose 										: 			tabClose,
        tabOpen 										: 			tabOpen,

        // zone realod
        zoneReload 										: 			zoneReload,

        // modal
        createModal										:			createModal,

        // Loading zone
        loadingZone										:			loadingZone,
        removeLoadingZone								:			removeLoadingZone,
        disableTab								        :			disableTab,
        enableTab								        :			enableTab,
        disableAllTabs								    :			disableAllTabs,
        enableAllTabs								    :			enableAllTabs,

    };

})();

var melisInstallerFormHelper = (function($, window) {
    var $body = window.parent.$("body");
    /**
     * KO NOTIFICATION for Multiple Form
     */
    function melisMultiKoNotification(errors, closeByButtonOnly) {
        if(!closeByButtonOnly) closeByButtonOnly = true;
        var closeByButtonOnly = ( closeByButtonOnly !== true ) ?  'overlay-hideonclick' : '';
        var errorTexts = '';
        $.each(errors, function(idx, errorData) {
            if(errorData['success'] === false) {
                errorTexts += '<h3>'+ (errorData['name']) +'</h3>';
                errorTexts +='<h4>'+ (errorData['message']) +'</h4>';
                highlightMultiErrors(errorData['success'], errorData['errors']);
                $.each( errorData['errors'], function( key, error ) {
                    if(key !== 'label'){
                        errorTexts += '<p class="modal-error-cont"><b>'+ (( error['label'] == undefined ) ? ((error['label']== undefined) ? key : errors['label'] ) : error['label'] )+ ': </b>  ';
                        // catch error level of object
                        try {
                            $.each( error, function( key, value ) {
                                if(key !== 'label' && key !== 'form'){
                                    $errMsg = '';
                                    if(value instanceof Object){
                                        $errMsg = value[0];
                                    }else{
                                        $errMsg = value;
                                    }
                                    errorTexts += '<span><i class="fa fa-circle"></i>'+ $errMsg + '</span>';
                                }
                            });
                        } catch(e) {
                            if(key !== 'label' && key !== 'form') {
                                errorTexts +=  '<span><i class="fa fa-circle"></i>'+ error + '</span>';
                            }
                        }
                    }
                });
                errorTexts += '</p><br/>';
            }
        });

        var div = '<div class="melis-modaloverlay '+ closeByButtonOnly +'"></div>';
        div += '<div class="melis-modal-cont KOnotif">  <div class="modal-content modal-setup-module-form-configuration"><div class="modal-setup-module-form-configuration-content">'+ errorTexts +' </div><div class="modal-btn-close-container"><span class="btn btn-block btn-primary">' + translators.tr_melis_installer_common_close +'</span></div></div> </div>';
        $body.append(div);
    }

    function highlightMultiErrors(success, errors){
        // remove red color for correctly inputted fields
        $body.find("#melis-installer-configuration-forms .form-group label").css("color", "inherit");
        // if all form fields are error color them red
        if(success == "false"){
            $.each( errors, function( key, error ) {
                $body.find("#melis-installer-configuration-forms .form-control[name='"+key +"']").parents(".form-group").find("label").css("color","red");
            });
        }
    }

    return {
        melisMultiKoNotification : melisMultiKoNotification
    }

})(jQuery, window);
