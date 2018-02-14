(function($) {
    // To avoid tab key on the last input on the form that cause a bus on owl carousel in setup
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            // Avoiding tab key
            if ($("form").find("input:last").is(":focus")) {
                objEvent.preventDefault(); // stops its action
            }
        }
    });

    var $body         = $("body");
    var isDbTested    = false;

    // ---=[ OWL SLIDER ]=---
    // owl slider [slide] event
    var $owl = $('.owl-carousel').owlCarousel({
        items: 1,
        touchDrag: false,
        mouseDrag: false,
        dotsSpeed: 500,
        navSpeed: 500,
        dots: false,
        //startPosition:7,
        // nav: true,
    });

    $body.find('[data-toggle="tooltip"]').tooltip({
        placement : 'left',
    });

    // FIRE EVENT AFTER SLIDER HAS FINISHED SLIDING
    $owl.on('changed.owl.carousel', function(event) {
        var slideNumber = event.item.index + 1

        // remove active class of the sidebar
        $(".sidebar .sidebarMenuWrapper > ul > li").removeClass("active");

        // add active class to the mirrored sidebar hadle
        $(".sidebar .sidebarMenuWrapper > ul > li:nth-child(" + slideNumber + ")").addClass("active");

        // disable Next Button on Database Connection, making sure that we test it before leaving
        $("#dbNext").removeClass("setup-pass-page btn btn-success");
        $("#dbNext").addClass("btn btn-default");
    });

    // SHOW / HIDE ADVANCE PROPERTIES ON ENVIRONEMENT CONFIG
    $body.on("click", ".melis-install-adv-prop-trigger", function(){
        if($(this).hasClass("fa-plus")){
            $(this).toggleClass("fa-plus fa-minus");
            $(this).closest(".form-group").next(".melis-install-adv-prop-content").slideDown();
        }else{
            $(this).toggleClass("fa-minus fa-plus");
            $(this).closest(".form-group").next(".melis-install-adv-prop-content").slideUp();
        }
    });


    // ENABLE / DISABLED MODULE CHECKBOXES
    var modules = $("#frmSelModules input[type='checkbox']");
    $.each( modules, function( key, value ) {
        if( $(this).is(":checked") ){
            $(this).prev().find(".cbmask-inner").addClass("cb-active");
        }
    });

    // Module checkboxes
    // Toggle single checkbox
    $body.on("click", ".cb-cont input[type=checkbox]", function(){
        if($(this).is(':checked')) {
            $(this).prop("checked", true);
            $(this).prev("span").find(".cbmask-inner").addClass('cb-active');
        }else{
            $(this).not(".requried-module").prop("checked", false);
            $(this).not(".requried-module").prev("span").find(".cbmask-inner").removeClass('cb-active');
        }
    });

    // global var
    var checkBox = $("#frmSelModules").find(".cb-cont input[type=checkbox]");

    function setModuleCheckboxStatus(status)
    {
        $.each($('.cb-cont input[type=checkbox]'), function(i, e) {
            if(false === status) {
                $(e).prop('checked', false)
                    .closest('.cb-cont').find(".cbmask-inner").removeClass('cb-active');
            }
            else {
                $(e).prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
            }
        });
    }

    // Toggle all checkboxes
    $body.on("click", "#chkSelectAllModules", function() {
        if($(this).is(':checked')) {
            $(checkBox).prop("checked", true);
            $("#frmSelModules").find(".cbmask-inner").addClass('cb-active');
        }else {
            $(checkBox).not(".requried-module").prop("checked", false);
            $(checkBox).not(".requried-module").prev("span").find(".cbmask-inner").removeClass('cb-active');
        }
    });

    // Toogle Select All
    $('.cb-cont input[type=checkbox]').change(function(){

        if( false == $("#weboption-democms").prop("checked") ) {
            //uncheck "select all", if one of the listed checkbox item is unchecked
            if(false == $(this).prop("checked")){ //if this item is unchecked
                //change "select all" checked status to false
                $("#chkSelectAllModules").prop('checked', false)
                    .closest('.cb-cont').find(".cbmask-inner").removeClass('cb-active');
            }


            //check "select all" if all checkbox items are checked
            if ($('.cb-cont input[type=checkbox]:checked').length == $('.cb-cont input[type=checkbox]').length - 1 ){
                $("#chkSelectAllModules").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
            }
        } else {
            $(".cb-cont input[type=checkbox][name=chkMelisCmsSlider]").prop('checked', true)
                .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
            // news
            $(".cb-cont input[type=checkbox][name=chkMelisCmsNews]").prop('checked', true)
                .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');

            // prospect
            $(".cb-cont input[type=checkbox][name=chkMelisCmsProspects]").prop('checked', true)
                .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');

            // cms
            $(".cb-cont input[type=checkbox][name=chkMelisCms]").prop('checked', true)
                .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
        }

    });

    $('.cb-cont input[type=checkbox]:not(#chkSelectAllModules)').click(function() {

        var name 		 = $(this).prop("name");
        var dependencies = $(this).data().dependency;
        var status 	     = $(this).prop('checked');
        var value 		 = $(this).val();
        dependencies = dependencies.split("|").map(function(v, i, a) {
            var n = v[i] = "chk"+v;
            return n;
        });

        if(dependencies) {

            if(true === status) {
                // switch all dependencies to true
                $.each(dependencies, function(i ,e) {
                    $(".cb-cont input[type=checkbox][name="+e+"]")
                        .prop('checked', true)
                        .closest('.cb-cont')
                        .find(".cbmask-inner")
                        .addClass('cb-active');
                });
            }
            else {
                // set all modules that are dependent to this module to false
                $.each($('.cb-cont input[type=checkbox]:not(#chkSelectAllModules)'), function(i, e) {
                    var dependents = $(e).data().dependency;
                    var found      = dependents.indexOf(value);
                    if(found !== -1) {
                        $(e).prop('checked', false)
                            .closest('.cb-cont')
                            .find(".cbmask-inner")
                            .removeClass('cb-active');
                    }
                });
            }
        }
    });

    $("body").on("click", "input[name='weboption']", function() {
        var value = $("input[name='weboption']:checked").val();

        switch(value) {
            case 'MelisDemoCms':
                $(".cb-cont input[type=checkbox][name=chkMelisCmsSlider]").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
                // news
                $(".cb-cont input[type=checkbox][name=chkMelisCmsNews]").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');

                // prospect
                $(".cb-cont input[type=checkbox][name=chkMelisCmsProspects]").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');

                // cms
                $(".cb-cont input[type=checkbox][name=chkMelisCms]").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');

                $("#module-selection").show();
                break;
            case 'MelisCoreOnly':
                setModuleCheckboxStatus(false);
                $("#module-selection").hide();
            break;
            default:
                $(".cb-cont input[type=checkbox][name=chkMelisCms]").prop('checked', true)
                    .closest('.cb-cont').find(".cbmask-inner").addClass('cb-active');
                $("#module-selection").show()
            break;
        }
    });


    // ---=[ SIDEBAR ]=---
    // sidebar menu click
    $body.on("click", '.setup-sidebar .sidebarMenuWrapper > ul > li', function(){
        if( $(this).hasClass("slide-active") ){
            var menuIndex = $(this).index();
            $owl.trigger('to.owl.carousel', [menuIndex, 500]);
        }
    });

    // ---=[ NEXT BUTTON CLICKS - EVENTS ]=---
    $body.on("click", ".setup-pass-page", function() {
        var currentPageButton = $(this).parents(".owl-item");
        var currentPage = $(".owl-carousel .owl-stage .owl-item").index(currentPageButton) + 1;

        // add class to sidebar button to activate and make it slidable
        $(".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child("+ (currentPage + 1) +")").addClass("slide-active");

        // disable Next Button on Database Connection, making sure that we test it before leaving
        $("#dbNext").removeClass("setup-pass-page btn btn-success");
        $("#dbNext").addClass("btn btn-default");


        // proceed to next slide
        if(currentPage === 2) {
            // System Configuration
            lazyNextButton();
            $.get('/melis/MelisInstaller/Installer/checkSysConfig', function(data) {
                if(data.success == 1) {
                    $owl.trigger('to.owl.carousel', [currentPage, 500]);
                }
                enableNextButton();
            });
        }
        else if(currentPage === 3) {
            // vhost rechecking
            lazyNextButton();
            $.get('/melis/MelisInstaller/Installer/checkVhostSetup', function(data) {
                if(data.success == 1) {
                    $owl.trigger('to.owl.carousel', [currentPage, 500]);
                }
                enableNextButton();
            });
        }
        else if(currentPage === 4) {
            // File System Rights Rechecking
            lazyNextButton();
            $.get('/melis/MelisInstaller/Installer/checkFileSystemRights', function(data) {
                if(data.success == 1) {
                    $owl.trigger('to.owl.carousel', [currentPage, 500]);
                }
                enableNextButton();
            });
        }
        else if(currentPage === 5) {
            addEnvironments();
        }
        else if(currentPage === 6) {
            $(".dbNext").removeClass("setup-pass-page");

            // Highlighting the Step 3 nav
            $('.hasSubmenu:eq( 2 )').removeClass("slide-active");
            $('.hasSubmenu:eq( 2 )').find("a").css("cursor", "default");
            $('.hasSubmenu:eq( 2 )').find("span").css("color","#fff");
            $('.hasSubmenu:eq( 2 )').find("i.fa").css("color","#fff");

            // Escaping slide 7 by adding the current page by 1
            var nextPage = currentPage + 1;
            var step3_1 = $('.hasSubmenu:eq( 2 )').next();
            step3_1.addClass("slide-active");

            $owl.trigger('to.owl.carousel', [nextPage, 500]);
        }
        else if(currentPage === 7) {
            // Escape and proceed to slide 8
        }
        else if(currentPage === 8) {
            // Website validation
            setDownloadableModules(currentPage);
        }
        else if(currentPage === 9) {
            lazyNextButton();
            getModuleConfiguration();
        }
        else if(currentPage === 10) {
            // Adding check mark on Step 3 nav after step 3.3
            submitModuleConfiguration();
        }
        else {
            $owl.trigger('to.owl.carousel', [currentPage, 500]);
        }


        // change circle icon in sidebar to green check icon
        $(".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child("+ currentPage +")").find("i").removeClass().addClass("fa fa-check fa-color-green");
        $(".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child("+ currentPage +")").find("span").css("color","#fff");
    });

    // ---=[ SETUP PAGE 1 - STEP 1.3 ]=---
    $body.on("click", ".setup-error-btn", function(){
        $(this).next(".setup-error-info").slideToggle();
    });

    // add new fields for add new environment
    $body.on("click", ".add-environment:not('.show-add-environment')", function(){
        var counter = $(".add-environment-container > div").length + 1;


        var environmentInputs = function () {
            var tmp = null;
            $.ajax({
                'async': false,
                'type': "GET",
                'global': false,
                'dataType': 'html',
                url:'/melis/MelisInstaller/Installer/new-environment-form?count=' + counter,
                'success': function (data) {
                    tmp = data;
                }
            });
            return tmp;
        }();
        $(environmentInputs).appendTo( $(this).next(".add-environment-container") );
        setOnOff();
    });

    // remove added environment fields
    $body.on("click", ".add-environment-container .environment-cont a.btn-danger", function(){
        var obj = $(this);
        var objParent = $(this).parents(".environment-cont");
        var env = $(this).parents(".environment-cont").find("input[type='text']").val();
        var url = $(this).parents(".environment-cont").find("input[type='url']").val();
        var dataString = [];
        dataString.push({
            name: 'env',
            value: env
        })
        dataString.push({
            name: 'url',
            value: url
        });
        dataString = $.param(dataString);
        if(url != "") {
            $.ajax({
                type        : 'POST',
                url         : '/melis/MelisInstaller/Installer/deleteEnvironment',
                data		: dataString,
                dataType    : 'json',
                encode		: true,
                success		: function(data){
                    if(data.success === 1) {
                        objParent.fadeOut(300, function(){
                            $(this).remove();
                        });
                    }
                    else {
                        alert('There was an error upon deleting an existing site domain');
                    }

                }
            });
        }
        else {
            $(this).parents(".environment-cont").fadeOut(300, function(){
                $(this).remove();
            });
        }

    });


    // ---=[ SETUP PAGE 2 ]=---
    // test database connection
    $body.on("click", "#test-db-connection", function(){
        var obj = $(this);
        var host = $(this).parents(".setup-content").find("input[name='host']").val();
        var database = $(this).parents(".setup-content").find("input[name='database']").val();

        var dataString = [];
        dataString.push({
            name: 'hostname',
            value: host,
        });
        dataString.push({
            name: 'database',
            value: database
        });
        dataString.push({
            name: 'username',
            value: $(this).parents(".setup-content").find("input[name='user']").val()
        });
        dataString.push({
            name: 'password',
            value: $(this).parents(".setup-content").find("input[name='password']").val()
        });

        $(".setup-modal-overlay, .setup-final-modal").fadeIn(300);
        $(".finish-text").hide();
        $(".testing-db-text").show();

        // color grey the input fields
        obj.parents(".setup-p2").find("#database-connection-form").find("label").css("color","#333");

        $.ajax({
            url         : '/melis/MelisInstaller/Installer/testDatabaseConnection',
            data        : dataString,
            type		: "POST",
            encode		: true
        }).success(function(data){

            $(".setup-modal-overlay, .setup-final-modal").fadeOut(300);

            // Success DB connection
            if(data.success === 1) {
                obj.parents(".setup-p2").find("#dbNext").removeClass("btn-default").addClass("btn-success setup-pass-page");
                isDbTested = true;
            }
            // Error DB connection
            else {
                obj.parents(".setup-p2").find("#dbNext").removeClass("btn-success setup-pass-page").addClass("btn-default");
                melisHelper.melisKoNotification(translators.tr_melis_installer_layout_dbcon_promp_title , translators.tr_melis_installer_layout_dbcon_promp_content, data.errors, 'closeByButtonOnly');

                if ('Host' in data.errors){
                    // HOST ERROR - color red the input fields
                    obj.parents(".setup-p2").find("#database-connection-form").find("input[name='host']").prev("label").css("color","red");
                }
                else if(data.errors.Collation == undefined){
                    // DB-USER-PASSWORD ERROR - color red the input fields
                    obj.parents(".setup-p2").find("#database-connection-form").find("input[name='database'], input[name='user'], input[name='password']").prev("label").css("color","red");
                }
            }

        }).error(function(xhr, textStatus, errorThrown){
            melisHelper.melisKoNotification(translators.tr_melis_installer_layout_dbcon_promp_title , translators.tr_melis_installer_layout_dbcon_promp_content, textStatus, 'closeByButtonOnly');
        });
    });

    // ---=[ FINISH PAGE - GATHER ALL DATA ]=---
    // test database connection
    $body.on("click", ".setup-creation .setup-pass-page.finish", function(){

        var myform = $('form');
        myform.find(':input:disabled').removeAttr('disabled');

        var serialized = myform.serialize();

        console.log(JSON.stringify(serialized, null, 4));

    });

    // Website Option action event
    $body.on("change", ".weboption-radio", function(){
        $(".setup3-webform").addClass("hidden");
        if($(this).val() == "NewSite"){
            $(".setup3-webform").removeClass("hidden");
        }
    });

    function ajaxRequest(url, dataString, callBack) {
        $.ajax({
            type: 'POST',
            url : url,
            data: dataString,
            dataType : 'json',
            encode: true,
            success: function(data) {
                callBack(data);
            }
        });
    }

    function getRequest(url, type, callBack) {
        $.ajax({
            type: 'GET',
            url : url,
            dataType : type,
            encode: true,
            success: function(data) {
                callBack(data);
            },
            error: function (request, status, error) {
                updateCmdText('<i class="fa fa-warning"></i> ' + translators.tr_melis_installer_cmd_ko);
            }
        });
    }

    function addEnvironments()
    {
        lazyNextButton();
        var dataString = $("#environment-form").serialize();
        ajaxRequest('/melis/MelisInstaller/Installer/newEnvironment', dataString, function(data) {
            if(data.success)
            {
                $owl.trigger('to.owl.carousel', [5, 500]);
            }
            else
            {
                alert("please review your entry");
            }
            enableNextButton();
        });
    }

    function addNewUser(nxtPage)
    {
        disableNextButton();
        var dataString = $("#idfrmuserdata").serialize();
        ajaxRequest('/melis/MelisInstaller/Installer/createNewUser', dataString, function(data) {
            if(data.success === 1)
            {
                $owl.trigger('to.owl.carousel', [nxtPage, 500]);
            }
            else
            {
                melisHelper.melisKoNotification(translators.tr_melis_installer_platform_modal_title , translators.tr_melis_installer_platform_modal_content, data.errors, 'closeByButtonOnly');
            }
            melisCoreTool.highlightErrors(data.success, data.errors, "idfrmuserdata");
            enableNextButton();
        });
    }

    function setDownloadableModules(nxtPage)
    {
        var packages = [];
        var modules  = [];

        var checkboxes = $("span.cb-modules-mask[class*='cb-active']");

        // populate packages
        $.each(checkboxes, function(i, v) {

            packages[i] = $(this).parents("span.cbmask-outer").next("input").data().package;
            modules[i]  = $(this).parents("span.cbmask-outer").next("input").val();
        });

        var selectedSite = $("input[name='weboption']:checked").val();
        var formWebLang  = $("form#idfrmweblang").serialize();
        var formWebData  = $("form#idfrmwebform").serialize();
        var data 		 = {packages : packages, modules : modules, site: selectedSite, siteLang: formWebLang, siteData: formWebData};



        disableNextButton();

        ajaxRequest('/melis/MelisInstaller/Installer/setDownloadableModules', data, function(response) {

            disableNextButton();
            $owl.trigger('to.owl.carousel', [8, 500]);

            $("body").find("#melis-installer-event-do-response").html('<span id="preloading-cont"><i class="fa fa-spinner fa-spin"></i> ' + translators.melis_installer_common_downloading + '</span>');
            setTimeout(function() {
                var vConsole = $("body").find("#melis-installer-event-do-response");
                var vConsoleText    = vConsole.html();
                var lastResponseLen = false;

                disableNextButton();
                $.ajax(
				{
					type: 'GET',
					url: '/melis/MelisInstaller/Installer/addModulesToComposer',
					dataType: "html",
					xhrFields: {
						onprogress: function(e) {
							$("#preloading-cont").remove();
							var vConsole      = $("body").find("#melis-installer-event-do-response");
							vConsole.html("");
							var vConsoleText  = vConsole.html();

							var curResponse, response = e.currentTarget.response;
							if(lastResponseLen === false) {
								curResponse = response;
								lastResponseLen = response.length;
							}
							else {
								curResponse = response.substring(lastResponseLen);
								lastResponseLen = response.length;
							}
							vConsoleText += curResponse + "\n";
							if(typeof vConsoleText !== "undefined") {

								vConsole.html(vConsoleText);

								// always scroll to bottom
								vConsole.animate({
									scrollTop: vConsole.prop("scrollHeight")
								}, 1115);
							}

						}
					},
					success: function(data) {

						// add downloader spinner
						$("#preloading-cont").remove();
						updateCmdText('<span id="preloading-cont"><i class="fa fa-spinner fa-spin"></i> ' + translators.melis_installer_common_downloading + '</span>');
					
						getRequest('/melis/MelisInstaller/Installer/downloadModules', 'html', function(response) {
							$("#preloading-cont").remove();
							vConsoleText = "" + vConsole.html() + "<br/>" + response;
							vConsole.html(vConsoleText + '<span id="cmd-imp-tbl"><i class="fa fa-spinner fa-spin"></i></span> ' + translators.melis_installer_module_import_tables + '<br/>');
							vConsole.animate({
								scrollTop: vConsole.prop("scrollHeight")
							}, 1115);

							// dbdeploy
							getRequest('/melis/MelisInstaller/Installer/execDbDeploy', 'html', function(response) {
								$("#cmd-imp-tbl").html('<i class="fa fa-info-circle"></i>');
								updateCmdText('<br/>' + response);

								// check for site installation
								updateCmdText('<br/><span id="cmd-chk-site"><i class="fa fa-spinner fa-spin"></i></span> ' + translators.melis_installer_site_checking + '<br/>');
								getRequest('/melis/MelisInstaller/Installer/checkSiteModule', 'json', function(response) {
									$("#cmd-chk-site").html('<i class="fa fa-info-circle"></i>');
									if(response.hasSite) {
										updateCmdText('<span id="cmd-site-install"><i class="fa fa-spinner fa-spin"></i></span> ' + translators.melis_installer_site_installing + '<br/>');
										// install site
										getRequest('/melis/MelisInstaller/Installer/installSiteModule', 'json', function(response) {
											$("#cmd-site-install").html('<i class="fa fa-info-circle"></i>');
											updateCmdText(response.message + '<br/>');

											// activate module
											updateCmdText('<br/><span id="cmd-act-mod"><i class="fa fa-spinner fa-spin"></i></span> ' + translators.melis_installer_activate_modules_notice + '<br/>');
											getRequest('/melis/MelisInstaller/Installer/rebuildAutoloader', 'html', function(rebuildAutoloaderResp) {
												setTimeout(function() {
													getRequest('/melis/MelisInstaller/Installer/activateModules', 'html', function(response) {
														$("#cmd-act-mod").html('<i class="fa fa-info-circle"></i>');
														updateCmdText(response + '<br/><i class="fa fa-info-circle"></i> ' + translators.melis_installer_common_done);
														getRequest('/melis/MelisInstaller/Installer/reprocessDbDeploy', 'json', function(reprocessDbDeployResp) {});
														enableNextButton();
													});

												}, 800);

											});
										});
									}
									else {
										// activate module
										updateCmdText('<br/><span id="cmd-act-mod"><i class="fa fa-spinner fa-spin"></i></span> ' + translators.melis_installer_activate_modules_notice + '<br/>');
										getRequest('/melis/MelisInstaller/Installer/rebuildAutoloader', 'html', function(rebuildAutoloaderResp) {
											setTimeout(function() {
												getRequest('/melis/MelisInstaller/Installer/activateModules', 'html', function(response) {
													$("#cmd-act-mod").html('<i class="fa fa-info-circle"></i>');
													updateCmdText(response + '<br/><i class="fa fa-info-circle"></i> ' + translators.melis_installer_common_done);
													getRequest('/melis/MelisInstaller/Installer/reprocessDbDeploy', 'json', function(reprocessDbDeployResp) {});
													enableNextButton();
												});

											}, 800);

										});
									}


								});


							});
						});


					},
                    error: function (request, status, error) {
                        updateCmdText('<i class="fa fa-warning"></i> ' + translators.tr_melis_installer_download_ko);
                    }
				});

            }, 800);

        });

    }

    function updateCmdText(text)
    {
        var vConsole     = $("body").find("#melis-installer-event-do-response");
        var vConsoleText = "" + vConsole.html();

        vConsole.html(vConsoleText + '<br/>' + text);
        vConsole.animate({
            scrollTop: vConsole.prop("scrollHeight")
        }, 1115);
    }

    function setWebConfig(nxtPage)
    {

        var packages = [];
        var modules  = [];

        // populate packages
        $.each($("input.requried-module"), function(i,v ) {
            packages[i] = $(this).data().package;
            modules[i]  = $(this).val();
        });

        disableNextButton();
        var webConfig = $("#setup-step3 form").serializeArray()
        ajaxRequest('/melis/MelisInstaller/Installer/setWebConfig', webConfig, function(webConfigRes) {

            if(webConfigRes.success !== 1)
            {
                melisHelper.melisKoNotification(translators.tr_melis_installer_platform_modal_title , translators.tr_melis_installer_platform_modal_content, webConfigRes.errors);
            }
            else
            {
                $owl.trigger('to.owl.carousel', [nxtPage, 500]);
            }

            // Unchecking Select all checkbox and modules checkboxes
            $("#chkSelectAllModules").prop("checked", false);
            $("#chkSelectAllModules").prev("span").find(".cbmask-inner").removeClass('cb-active');
            $("#frmSelModules").find(".cb-cont input[type=checkbox]").prop("checked", false);
            $("#frmSelModules").find(".cb-cont input[type=checkbox]").prev("span").find(".cbmask-inner").removeClass('cb-active');

            $.each(webConfigRes.requiredModules, function(index , val){
                $("#frmSelModules").find(".cb-cont input[name=chk"+val+"]").prop("checked", true);
                $("#frmSelModules").find(".cb-cont input[name=chk"+val+"]").addClass("requried-module");
                $("#frmSelModules").find(".cb-cont input[name=chk"+val+"]").prev("span").find(".cbmask-inner").addClass('cb-active');
            });

            melisCoreTool.highlightErrors(webConfigRes.success, webConfigRes.errors, "setup-step3 form");
            enableNextButton();
        });
    }

    function enableNextButton()
    {
        $(".setup-pass-page").removeAttr("disabled");
        $(".setup-pass-page").removeClass("btn-default disabled");
        $(".setup-pass-page").addClass("btn-success");
        $(".setup-pass-page").html(translators.tr_melis_installer_common_next);
    }

    function disableNextButton()
    {
        $(".setup-pass-page").attr("disabled", "disabled");
        $(".setup-pass-page").removeClass("btn-success");
        $(".setup-pass-page").addClass("btn-default");
    }

    function lazyNextButton()
    {
        $(".setup-pass-page").attr("disabled", "disabled");
        $(".setup-pass-page").removeClass("btn-success");
        $(".setup-pass-page").addClass("btn-default");
        $(".setup-pass-page").html('<i class="fa fa-spinner fa-spin"></i> ' + translators.melis_installer_common_checking);
    }

    function processSelectedModules(nextPage)
    {
        var dataString = $("#frmSelModules").serializeArray();
        var modDataString = [];
        if(dataString !== null) {
            $.each(dataString, function(i,v) {
                modDataString.push({
                    name: v.name,
                    value: v.value
                });
            });
        }
        modDataString.push({
            name: "_default",
            value: "default"
        });

        modDataString = $.param(modDataString);
        ajaxRequest('/melis/MelisInstaller/Installer/addInstallableModules', modDataString, function(data) {
            $owl.trigger('to.owl.carousel', [nextPage, 500]);
        });

    }

    function getModuleConfiguration()
    {

        getRequest('/melis/MelisInstaller/Installer/getModuleConfigurationForms', 'html', function(data) {
            $("#melis-installer-configuration-forms").html(data);
            $("i[data-toggle='tooltip']").tooltip();
            $owl.trigger('to.owl.carousel', [9, 500]);
        });
    }

    function submitModuleConfiguration()
    {
        lazyNextButton();
        $('.hasSubmenu:eq( 2 )').find("i").removeClass("fa-circle-o").css("color","").addClass("fa fa-check fa-color-green");
        var forms = $("#melis-installer-configuration-forms form").serialize();
        getRequest('/melis/MelisInstaller/Installer/validateModuleConfigurationForm?'+forms, 'json', function(response) {
            if(response.success == '1') {
				getRequest('/melis/MelisInstaller/Installer/submitModuleConfigurationForm?'+forms, 'json', function(resp) {
					if(resp.success == '1') {
                        enableNextButton();
						$owl.trigger('to.owl.carousel', [10, 500]);
					}
					else {
						melisInstallerFormHelper.melisMultiKoNotification(response.errors);
					}
				})
            }
            else {
                melisInstallerFormHelper.melisMultiKoNotification(response.errors);
                enableNextButton();
            }

        });
    }


    // show modal spinner after you click the finish button
    $("body").on("click", ".setup-finish", function(){
        if(isDbTested) {

            disableNextButton();

            $(".setup-finish").html(translators.tr_melis_installer_common_installing);


            $.get('/melis/MelisInstaller/Installer/finalizeSetup', function(data) {
                if(data.success === 1){
                    $(".setup-modal-overlay, .setup-final-modal").fadeIn(300);
                    $(".finish-text").show();
                    $(".testing-db-text").hide();

                    setTimeout(function() {
                        location.href = "/melis";
                    }, 3000);

                }
                else {
                    alert(translators.tr_melis_installer_common_finish_error);
                }
            });


        }
        else {
            $owl.trigger('to.owl.carousel', [5, 500]);
        }
    });


})(jQuery);

function changeSetupLanguage(locale) {
    var datastring = { langLocale: locale };
    console.log(locale);
    $.ajax({
        type        : 'POST',
        url         : '/melis/MelisInstaller/Installer/changeLang',
        data        : datastring,
        dataType    : 'json',
        encode      : true
    }).success(function(data){
        if (data.success){
            location.reload();
        }
        console.log(data);
    });

}