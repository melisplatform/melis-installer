$(window).on("load", function () {
	$("#content").css("visibility", "visible");
});
(function ($) {
	var main_dependencies = "";

	// To avoid tab key on the last input on the form that cause a bus on owl carousel in setup
	$(document).on("keydown", function (objEvent) {
		if (objEvent.key == 'Tab') {
			//tab pressed
			// Avoiding tab key
			if ($("form").find("input:last").is(":focus")) {
				objEvent.preventDefault(); // stops its action
			}
		}
	});

	var $body = $("body");
	var isDbTested = false;

	// ---=[ OWL SLIDER ]=---
	// owl slider [slide] event
	var $owl = $(".owl-carousel").owlCarousel({
		items: 1,
		touchDrag: false,
		mouseDrag: false,
		dotsSpeed: 500,
		navSpeed: 500,
		dots: false,
		// startPosition:7,
	});

	/* $body.find('[data-bs-toggle="tooltip"]').tooltip({
		placement: "left",
	}); */

	// FIRE EVENT AFTER SLIDER HAS FINISHED SLIDING
	$owl.on("changed.owl.carousel", function (event) {
		var slideNumber = event.item.index + 1;

		// remove active class of the sidebar
		$(".sidebar .sidebarMenuWrapper > ul > li").removeClass("active");

		// add active class to the mirrored sidebar hadle
		$(
			".sidebar .sidebarMenuWrapper > ul > li:nth-child(" + slideNumber + ")"
		).addClass("active");

		// disable Next Button on Database Connection, making sure that we test it before leaving
		$("#dbNext").removeClass("setup-pass-page btn btn-success");
		$("#dbNext").addClass("btn btn-default");
	});

	// SHOW / HIDE ADVANCE PROPERTIES ON ENVIRONEMENT CONFIG
	$body.on("click", ".melis-install-adv-prop-trigger", function () {
		if ($(this).hasClass("fa-plus")) {
			$(this).toggleClass("fa-plus fa-minus");
			$(this)
				.closest(".form-group")
				.next(".melis-install-adv-prop-content")
				.slideDown();
		} else {
			$(this).toggleClass("fa-minus fa-plus");
			$(this)
				.closest(".form-group")
				.next(".melis-install-adv-prop-content")
				.slideUp();
		}
	});

	// ENABLE / DISABLED MODULE CHECKBOXES
	var modules = $("#frmSelModules input[type='checkbox']");
	$.each(modules, function (key, value) {
		if ($(this).is(":checked")) {
			$(this).prev().find(".cbmask-inner").addClass("cb-active");
		}
	});

	// Module checkboxes
	// Toggle single checkbox
	$body.on("click", ".cb-cont input[type=checkbox]", function (e) {
		// prevent uncheck on main dependencies based on setup
		var dependencies = main_dependencies.split("|");

		if (!dependencies.includes($(this).val())) {
			if ($(this).is(":checked")) {
				$(this).prop("checked", true);
				$(this).prev("span").find(".cbmask-inner").addClass("cb-active");
			} else {
				$(this).not(".requried-module").prop("checked", false);
				$(this)
					.not(".requried-module")
					.prev("span")
					.find(".cbmask-inner")
					.removeClass("cb-active");
			}
		}
	});

	// global var
	var checkBox = $("#frmSelModules").find(".cb-cont input[type=checkbox]");

	function setModuleCheckboxStatus(status) {
		$.each($(".cb-cont input[type=checkbox]"), function (i, e) {
			if (false === status) {
				$(e)
					.prop("checked", false)
					.closest(".cb-cont")
					.find(".cbmask-inner")
					.removeClass("cb-active");
			} else {
				$(e)
					.prop("checked", true)
					.closest(".cb-cont")
					.find(".cbmask-inner")
					.addClass("cb-active");
			}
		});
	}

	// Toggle all checkboxes
	$body.on("click", "#chkSelectAllModules", function () {
		if ($(this).is(":checked")) {
			$(checkBox).prop("checked", true);
			$("#frmSelModules").find(".cbmask-inner").addClass("cb-active");
		} else {
			$(checkBox).not(".requried-module").prop("checked", false);
			$(checkBox)
				.not(".requried-module")
				.prev("span")
				.find(".cbmask-inner")
				.removeClass("cb-active");
			dependencyChecker($("input[name='weboption']:checked"));
		}
	});

	$("input.site-selection-radio").on("change", function () {
		dependencyChecker($(this));
	});

	// Site to install radio button
	$body.on("click", "input[name='weboption']", function () {
		// reset checkbox
		$("#chkSelectAllModules").prop("checked", false);
		$("#frmSelector").find(".cbmask-inner").removeClass("cb-active");
		$(checkBox).not(".requried-module").prop("checked", false);
		$(checkBox)
			.not(".requried-module")
			.prev("span")
			.find(".cbmask-inner")
			.removeClass("cb-active");

		var value = $("input[name='weboption']:checked").val();
		// list of main dependencies based on selected setup
		main_dependencies = $(this).data().dependency;

		if (value == "MelisCoreOnly") {
			$("#module-selection").slideUp();
		} else {
			$("#module-selection").slideDown();
		}
		dependencyChecker($(this));
	});

	$(".setup-button-cont").on("click", function () {
		if ($(".module-selection").length) {
			if ($("input[name='weboption']:checked").length) {
				dependencyChecker($("input[name='weboption']:checked"));
			}
		}
	});

	// Toogle Select All
	$(".cb-cont input[type=checkbox]").on("change", function () {
		var dependencies = main_dependencies.split("|");

		if (!dependencies.includes($(this).val())) {
			//uncheck "select all", if one of the listed checkbox item is unchecked
			if (false == $(this).prop("checked")) {
				//change "select all" checked status to false
				$("#chkSelectAllModules")
					.prop("checked", false)
					.closest(".cb-cont")
					.find(".cbmask-inner")
					.removeClass("cb-active");
			}

			//check "select all" if all checkbox items are checked
			if (
				$(".cb-cont input[type=checkbox]:checked").length ==
				$(".cb-cont input[type=checkbox]").length - 1
			) {
				$("#chkSelectAllModules")
					.prop("checked", true)
					.closest(".cb-cont")
					.find(".cbmask-inner")
					.addClass("cb-active");
			}
			dependencyChecker($("input[name='weboption']:checked"));
		}
	});

	/*
	 * Check dependency
	 * @var dom selector
	 * @return null
	 */
	function dependencyChecker(dom) {
		var name = dom.prop("name");
		var dependencies = dom.data().dependency;
		var status = dom.prop("checked");
		var value = dom.val();

		dependencies = dependencies.split("|").map(function (v, i, a) {
			var n = (v[i] = "chk" + v);
			return n;
		});

		if (dependencies) {
			if (true === status) {
				// switch all dependencies to true
				$.each(dependencies, function (i, e) {
					$(".cb-cont input[type=checkbox][name=" + e + "]")
						.prop("checked", true)
						.closest(".cb-cont")
						.find(".cbmask-inner")
						.addClass("cb-active");
				});
			} else {
				// set all modules that are dependent to this module to false
				$.each(
					$(".cb-cont input[type=checkbox]:not(#chkSelectAllModules)"),
					function (i, e) {
						var dependents = $(e).data().dependency;
						var found = dependents.indexOf(value);
						if (found !== -1) {
							$(e)
								.prop("checked", false)
								.closest(".cb-cont")
								.find(".cbmask-inner")
								.removeClass("cb-active");
						}
					}
				);
			}
		}
	}

	$(".cb-cont input[type=checkbox]:not(#chkSelectAllModules)").on("click",
		function () {
			var dependencies = main_dependencies.split("|");

			if (!dependencies.includes($(this).val())) {
				dependencyChecker($(this));
			}
		}
	);

	// ---=[ SIDEBAR ]=---
	// sidebar menu click
	$body.on(
		"click",
		".setup-sidebar .sidebarMenuWrapper > ul > li",
		function () {
			if ($(this).hasClass("slide-active")) {
				var menuIndex = $(this).index();
				$owl.trigger("to.owl.carousel", [menuIndex, 500]);
			}
		}
	);

	// ---=[ NEXT BUTTON CLICKS - EVENTS ]=---
	$body.on("click", ".setup-pass-page", function () {
		var currentPageButton = $(this).parents(".owl-item");
		var currentPage =
			$(".owl-carousel .owl-stage .owl-item").index(currentPageButton) + 1;

		// add class to sidebar button to activate and make it slidable
		$(
			".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child(" +
				(currentPage + 1) +
				")"
		).addClass("slide-active");

		// disable Next Button on Database Connection, making sure that we test it before leaving
		$("#dbNext").removeClass("setup-pass-page btn btn-success");
		$("#dbNext").addClass("btn btn-default");

		// proceed to next slide
		if (currentPage === 2) {
			// System Configuration
			lazyNextButton();
			$.get("/melis/MelisInstaller/Installer/checkSysConfig", function (data) {
				if (data.success == 1) {
					$owl.trigger("to.owl.carousel", [currentPage, 500]);
				}
				enableNextButton();
			});
		} else if (currentPage === 3) {
			// apache rechecking
			lazyNextButton();
			$.get(
				"/melis/MelisInstaller/Installer/checkApacheSetup",
				function (data) {
					if (data.success == 1) {
						$owl.trigger("to.owl.carousel", [currentPage, 500]);
					}
					enableNextButton();
				}
			);
		} else if (currentPage === 4) {
			// vhost rechecking
			lazyNextButton();
			$.get("/melis/MelisInstaller/Installer/checkVhostSetup", function (data) {
				if (data.success == 1) {
					$owl.trigger("to.owl.carousel", [currentPage, 500]);
				}
				enableNextButton();
			});
		} else if (currentPage === 5) {
			// File System Rights Rechecking
			lazyNextButton();
			$.get(
				"/melis/MelisInstaller/Installer/checkFileSystemRights",
				function (data) {
					if (data.success == 1) {
						$owl.trigger("to.owl.carousel", [currentPage, 500]);
					}
					enableNextButton();
				}
			);
		} else if (currentPage === 6) {
			addEnvironments(currentPage);
		} else if (currentPage === 7) {
			$(".dbNext").removeClass("setup-pass-page");

			// Highlighting the Step 3 nav
			$(".hasSubmenu:eq( 2 )").removeClass("slide-active");
			$(".hasSubmenu:eq( 2 )").find("a").css("cursor", "default");
			$(".hasSubmenu:eq( 2 )").find("span").css("color", "#fff");
			$(".hasSubmenu:eq( 2 )").find("i.fa").css("color", "#fff");

			// Escaping slide 7 by adding the current page by 1
			var nextPage = currentPage + 1;
			var step3_1 = $(".hasSubmenu:eq( 2 )").next();
			step3_1.addClass("slide-active");

			$owl.trigger("to.owl.carousel", [nextPage, 500]);
		} else if (currentPage === 8) {
			// Escape and proceed to slide 8
		} else if (currentPage === 9) {
			// Website validation
			setDownloadableModules(currentPage);
		} else if (currentPage === 10) {
			lazyNextButton();
			getRequest(
				"/melis/MelisInstaller/Installer/reprocessDbDeploy",
				"json",
				function(reprocessDbDeployResp) {
					getModuleConfiguration(currentPage);
				},
				false,
				function() {
					getModuleConfiguration(currentPage);
				}
			);
		} else if (currentPage === 11) {
			// Adding check mark on Step 3 nav after step 3.3
			submitModuleConfiguration(currentPage);
		} else {
			$owl.trigger("to.owl.carousel", [currentPage, 500]);
		}

		// change circle icon in sidebar to green check icon
		$(
			".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child(" +
				currentPage +
				")"
		)
			.find("i")
			.removeClass()
			.addClass("fa fa-check fa-color-green");
		$(
			".setup-sidebar .sidebarMenuWrapper > ul > li:nth-child(" +
				currentPage +
				")"
		)
			.find("span")
			.css("color", "#fff");
	});

	// ---=[ SETUP PAGE 1 - STEP 1.3 ]=---
	$body.on("click", ".setup-error-btn", function () {
		$(this).next(".setup-error-info").slideToggle();
	});

	// add new fields for add new environment
	$body.on(
		"click",
		".add-environment:not('.show-add-environment')",
		function () {
			var counter = $(".add-environment-container > div").length + 1;

			var environmentInputs = (function () {
				var tmp = null;
				$.ajax({
					async: false,
					type: "GET",
					global: false,
					dataType: "html",
					url:
						"/melis/MelisInstaller/Installer/new-environment-form?count=" +
						counter
				}).done(function (data) {
					tmp = data;
				});
				return tmp;
			})();
			$(environmentInputs).appendTo($(this).next(".add-environment-container"));
			setOnOff();
		}
	);

	// remove added environment fields
	$body.on(
		"click",
		".add-environment-container .environment-cont a.btn-danger",
		function () {
			var obj = $(this);
			var objParent = $(this).parents(".environment-cont");
			var env = $(this)
				.parents(".environment-cont")
				.find("input[type='text']")
				.val();
			var url = $(this)
				.parents(".environment-cont")
				.find("input[type='url']")
				.val();
			var dataString = [];
			dataString.push({
				name: "env",
				value: env,
			});
			dataString.push({
				name: "url",
				value: url,
			});
			dataString = $.param(dataString);
			$(this)
				.parents(".environment-cont")
				.fadeOut(300, function () {
					$(this).remove();
				});
		}
	);

	// ---=[ SETUP PAGE 2 ]=---
	// test database connection
	$body.on("click", "#test-db-connection", function(e) {
		var obj = $(this);
		var host = $(this)
			.parents(".setup-content")
			.find("input[name='host']")
			.val();
		var database = $(this)
			.parents(".setup-content")
			.find("input[name='database']")
			.val();

		var dataString = [];
		dataString.push({
			name: "hostname",
			value: host,
		});
		dataString.push({
			name: "database",
			value: database,
		});
		dataString.push({
			name: "username",
			value: $(this).parents(".setup-content").find("input[name='user']").val(),
		});
		dataString.push({
			name: "password",
			value: $(this)
				.parents(".setup-content")
				.find("input[name='password']")
				.val(),
		});

		$(".setup-modal-overlay, .setup-final-modal").fadeIn(300);
		$(".finish-text").hide();
		$(".testing-db-text").show();

		// color grey the input fields
		obj
			.parents(".setup-p2")
			.find("#database-connection-form")
			.find("label")
			.css("color", "#333");

		$.ajax({
			url: "/melis/MelisInstaller/Installer/testDatabaseConnection",
			data: dataString,
			type: "POST",
			encode: true,
		})
		.done(function (data) {
			$(".setup-modal-overlay, .setup-final-modal").fadeOut(300);

			// Success DB connection
			if (data.success === 1) {
				obj
					.parents(".setup-p2")
					.find("#dbNext")
					.removeClass("btn-default")
					.addClass("btn-success setup-pass-page");
				isDbTested = true;
			}
			// Error DB connection
			else {
				obj
					.parents(".setup-p2")
					.find("#dbNext")
					.removeClass("btn-success setup-pass-page")
					.addClass("btn-default");
				melisHelper.melisKoNotification(
					translators.tr_melis_installer_layout_dbcon_promp_title,
					translators.tr_melis_installer_layout_dbcon_promp_content,
					data.errors,
					"closeByButtonOnly"
				);

				if ("Host" in data.errors) {
					// HOST ERROR - color red the input fields
					obj
						.parents(".setup-p2")
						.find("#database-connection-form")
						.find("input[name='host']")
						.prev("label")
						.css("color", "red");
				} else if (data.errors.Collation == undefined) {
					// DB-USER-PASSWORD ERROR - color red the input fields
					obj
						.parents(".setup-p2")
						.find("#database-connection-form")
						.find(
							"input[name='database'], input[name='user'], input[name='password']"
						)
						.prev("label")
						.css("color", "red");
				}
			}
		})
		.fail(function (xhr, textStatus, errorThrown) {
			melisHelper.melisKoNotification(
				translators.tr_melis_installer_layout_dbcon_promp_title,
				translators.tr_melis_installer_layout_dbcon_promp_content,
				textStatus,
				"closeByButtonOnly"
			);
		});

		e.preventDefault();
	});

	/* JS for Installing Other Framework */
	$body.on(
		"click",
		".enable_fw_container input[name='enable_multi_fw']",
		function () {
			if ($(this).val() === "true") {
				$(".other_framework_form_container").slideDown();
			} else {
				$(".other_framework_form_container").slideUp();
			}

			$("#id_otherframework_form")
				.find("#id_enable_multi_fw")
				.val($(this).val());
		}
	);
	/* end */

	// ---=[ FINISH PAGE - GATHER ALL DATA ]=---
	// test database connection
	$body.on("click", ".setup-creation .setup-pass-page.finish", function () {
		var myform = $("form");
		myform.find(":input:disabled").prop("disabled", false);

		var serialized = myform.serialize();

		console.log(JSON.stringify(serialized, null, 4));
	});

	// Website Option action event
	$body.on("change", ".weboption-radio", function () {
		$("#site-install-container").addClass("hidden");
		$(".setup3-webform").slideUp();

		if ($(this).val() == "NewSite") {
			$(".setup3-webform").slideDown();
		}

		if ($(this).val() == "MelisDemoCms") {
			$("#site-install-container").removeClass("hidden");
		}
	});

	function ajaxRequest(url, dataString, callBack) {
		$.ajax({
			type: "POST",
			url: url,
			data: dataString,
			dataType: "json",
			encode: true
		}).done(function (data) {
			callBack(data);
		});
	}

	function getRequest(url, type, callBack, logError, errorCallBack) {
		$.ajax({
			type: "GET",
			url: url,
			dataType: type,
			encode: true
		}).done(function (data) {
			callBack(data);
		}).fail(function (xhr, textStatus, errorThrown) {
			var logError = logError || false;
				if (logError === true) {
					updateCmdText(
						'<i class="fa fa-warning"></i> ' +
							translators.tr_melis_installer_cmd_ko
					);
				}
				
				if ( errorCallBack !== undefined || errorCallBack !== null) {
					if (errorCallBack) {
						errorCallBack();
					}
				}

				console.log("ERROR !! Status = " + textStatus + "\nError = " + errorThrown + "\nxhr = " + xhr + "\nxhr.statusText = " + xhr.statusText);
		});
	}

	function addEnvironments(currentPage) {
		lazyNextButton();
		var dataString = $("#environment-form").serialize();
		ajaxRequest(
			"/melis/MelisInstaller/Installer/newEnvironment",
			dataString,
			function (data) {
				if (data.success) {
					$owl.trigger("to.owl.carousel", [currentPage, 500]);
				} else {
					alert("please review your entry");
				}
				enableNextButton();
			}
		);
	}

	function addNewUser(nxtPage) {
		disableNextButton();
		var dataString = $("#idfrmuserdata").serialize();
		ajaxRequest(
			"/melis/MelisInstaller/Installer/createNewUser",
			dataString,
			function (data) {
				if (data.success === 1) {
					$owl.trigger("to.owl.carousel", [nxtPage, 500]);
				} else {
					melisHelper.melisKoNotification(
						translators.tr_melis_installer_platform_modal_title,
						translators.tr_melis_installer_platform_modal_content,
						data.errors,
						"closeByButtonOnly"
					);
				}
				melisCoreTool.highlightErrors(
					data.success,
					data.errors,
					"idfrmuserdata"
				);
				enableNextButton();
			}
		);
	}

	function setDownloadableModules(currentPage) {
		var packages = [];
		var modules = [];

		var checkboxes = $("span.cb-modules-mask[class*='cb-active']");

		// populate packages
		$.each(checkboxes, function (i, v) {
			packages[i] = $(this)
				.parents("span.cbmask-outer")
				.next("input")
				.data().package;
			modules[i] = $(this).parents("span.cbmask-outer").next("input").val();
		});

		var site = $("input[name=site]:checked");
		var siteModule = "";

		if (
			site.length &&
			$("input[name='weboption']:checked").val() === "MelisDemoCms"
		) {
			packages[packages.length] = site.val();
			modules[modules.length] = siteModule = site.data().module;
		}

		// add the selected site
		var formWebLang = $("form#idfrmweblang").serialize();
		var selectedSite =
			$("input[name='weboption']:checked").val() == "MelisDemoCms"
				? siteModule
				: $("input[name='weboption']:checked").val();
		var formWebData = $("form#idfrmwebform").serialize();
		//add other framework form data
		var otherFWFOrmData = $("form#id_otherframework_form").serialize();
		var data = {
			packages: packages,
			modules: modules,
			site: selectedSite,
			siteLang: formWebLang,
			siteData: formWebData,
			otherFWData: otherFWFOrmData,
		};

		disableNextButton();

		ajaxRequest(
			"/melis/MelisInstaller/Installer/setDownloadableModules",
			data,
			function (response) {
				/**
				 * Check for errors
				 */
				if (!response["success"]) {
					melisHelper.melisKoNotification(
						"Installing other framework",
						response["message"],
						[]
					);
					enableNextButton();
				} else {
					disableNextButton();
					$owl.trigger("to.owl.carousel", [currentPage, 500]);

					$("body")
						.find("#melis-installer-event-do-response")
						.html(
							'<span id="preloading-cont"><i class="fa fa-spinner fa-spin"></i> ' +
								translators.melis_installer_common_downloading +
								"</span>"
						);
					setTimeout(function () {
						var vConsole = $("body").find("#melis-installer-event-do-response");
						var vConsoleText = vConsole.html();
						var lastResponseLen = false;
						var isMultiFramework = false;

						disableNextButton();
						$.ajax({
							type: "GET",
							url: "/melis/MelisInstaller/Installer/addModulesToComposer",
							dataType: "html",
							xhrFields: {
								onprogress: function (e) {
									$("#preloading-cont").remove();
									var vConsole = $("body").find(
										"#melis-installer-event-do-response"
									);
									vConsole.html("");
									var vConsoleText = vConsole.html();

									var curResponse,
										response = e.currentTarget.response;
									if (lastResponseLen === false) {
										curResponse = response;
										lastResponseLen = response.length;
									} else {
										curResponse = response.substring(lastResponseLen);
										lastResponseLen = response.length;
									}
									vConsoleText += curResponse + "\n";
									if (typeof vConsoleText !== "undefined") {
										vConsole.html(vConsoleText);

										// always scroll to bottom
										vConsole.animate(
											{
												scrollTop: vConsole.prop("scrollHeight"),
											},
											1115
										);
									}
								},
							}
						}).done(function (data) {
							// add downloader spinner
							$("#preloading-cont").remove();
							updateCmdText(
								'<span id="preloading-cont"><i class="fa fa-spinner fa-spin"></i> ' +
									translators.melis_installer_common_downloading +
									"</span>"
							);

							getRequest(
								"/melis/MelisInstaller/Installer/downloadModules",
								"html",
								function (response) {
									$("#preloading-cont").remove();
									vConsoleText = "" + vConsole.html() + "<br/>" + response;
									vConsole.html(
										vConsoleText +
											'<span id="cmd-imp-tbl"><i class="fa fa-spinner fa-spin"></i></span> ' +
											translators.melis_installer_module_import_tables +
											"<br/>"
									);
									vConsole.animate(
										{
											scrollTop: vConsole.prop("scrollHeight"),
										},
										1115
									);

									// dbdeploy
									getRequest(
										"/melis/MelisInstaller/Installer/execDbDeploy",
										"html",
										function (response) {
											$("#cmd-imp-tbl").html(
												'<i class="fa fa-info-circle"></i>'
											);
											updateCmdText("<br/>" + response);

											// check for site installation
											getRequest(
												"/melis/MelisInstaller/Installer/checkSiteModule",
												"json",
												function (response) {
													//check if multi framework
													isMultiFramework = response.isMultiFramework;

													if (response.hasSite) {
														updateCmdText(
															'<span id="cmd-site-install"><i class="fa fa-spinner fa-spin"></i></span> ' +
																translators.melis_installer_site_installing +
																"<br/>"
														);
														// install site
														getRequest(
															"/melis/MelisInstaller/Installer/installSiteModule",
															"json",
															function (response) {
																$("#cmd-site-install").html(
																	'<i class="fa fa-info-circle"></i>'
																);
																updateCmdText(response.message + "<br/>");

																// activate module
																updateCmdText(
																	'<br/><span id="cmd-act-mod"><i class="fa fa-spinner fa-spin"></i></span> ' +
																		translators.melis_installer_activate_modules_notice +
																		"<br/>"
																);
																getRequest(
																	"/melis/MelisInstaller/Installer/rebuildAutoloader",
																	"html",
																	function (rebuildAutoloaderResp) {
																		setTimeout(function () {
																			getRequest(
																				"/melis/MelisInstaller/Installer/activateModules",
																				"html",
																				function (response) {
																					$("#cmd-act-mod").html(
																						'<i class="fa fa-info-circle"></i>'
																					);

																					executeOtherProcess(
																						isMultiFramework,
																						response
																					);
																				}
																			);
																		}, 800);
																	}
																);
															}
														);
													} else {
														// activate module
														updateCmdText(
															'<br/><span id="cmd-act-mod"><i class="fa fa-spinner fa-spin"></i></span> ' +
																translators.melis_installer_activate_modules_notice +
																"<br/>"
														);
														getRequest(
															"/melis/MelisInstaller/Installer/rebuildAutoloader",
															"html",
															function (rebuildAutoloaderResp) {
																setTimeout(function () {
																	getRequest(
																		"/melis/MelisInstaller/Installer/activateModules",
																		"html",
																		function (response) {
																			$("#cmd-act-mod").html(
																				'<i class="fa fa-info-circle"></i>'
																			);

																			executeOtherProcess(
																				isMultiFramework,
																				response
																			);
																		}
																	);
																}, 800);
															}
														);
													}
												}
											);
										}
									);
								}
							);
						}).fail(function (request, status, error) {
							updateCmdText(
								'<i class="fa fa-warning"></i> ' +
									translators.tr_melis_installer_download_ko
							);
						});
					}, 800);
				}
			}
		);
	}

	function executeOtherProcess(isMultiFramework, response) {
		if (isMultiFramework === true) {
			//download third party framework
			updateCmdText(
				"<br/>" +
					response +
					'<br/><span id="cmd-download-fm"><i class="fa fa-spinner fa-spin"></i></span> ' +
					translators.tr_melis_installer_download_thirdparty_fw_notice +
					"<br/>"
			);
			setTimeout(function () {
				getRequest(
					"/melis/MelisInstaller/Installer/downloadFrameworkSkeleton",
					"html",
					function (response) {
						$("#cmd-download-fm").html('<i class="fa fa-info-circle"></i>');

						updateCmdText(
							"<br/>" +
								response +
								'<br/><span id="cmd-finalize"><i class="fa fa-spinner fa-spin"></i> ' +
								translators.tr_melis_installer_common_finalize +
								"</span><br/>"
						);
						getRequest(
							"/melis/MelisInstaller/Installer/reprocessDbDeploy",
							"json",
							function (reprocessDbDeployResp) {
								$("#cmd-finalize").html(
									'<i class="fa fa-info-circle"></i> ' +
										translators.melis_installer_common_done
								);
								enableNextButton();
							},
							false,
							function () {
								$("#cmd-finalize").html(
									'<i class="fa fa-info-circle"></i> ' +
										translators.melis_installer_common_done
								);
								enableNextButton();
							}
						);
					}
				);
			}, 500);
		} else {
			updateCmdText(
				"<br/>" +
					response +
					'<br/><span id="cmd-finalize"><i class="fa fa-spinner fa-spin"></i> ' +
					translators.tr_melis_installer_common_finalize +
					"</span><br/>"
			);
			getRequest(
				"/melis/MelisInstaller/Installer/reprocessDbDeploy",
				"json",
				function (reprocessDbDeployResp) {
					$("#cmd-finalize").html(
						'<i class="fa fa-info-circle"></i> ' +
							translators.melis_installer_common_done
					);
					document.querySelector(".setup-p3 .setup-pass-page").removeAttribute("disabled");
					enableNextButton();
				},
				false,
				function () {
					$("#cmd-finalize").html(
						'<i class="fa fa-info-circle"></i> ' +
							translators.melis_installer_common_done
					);
					enableNextButton();
				}
			);
		}
	}

	function updateCmdText(text) {
		var vConsole = $("body").find("#melis-installer-event-do-response");
		var vConsoleText = "" + vConsole.html();

		vConsole.html(vConsoleText + "<br/>" + text);
		vConsole.animate(
			{
				scrollTop: vConsole.prop("scrollHeight"),
			},
			1115
		);
	}

	function setWebConfig(nxtPage) {
		var packages = [];
		var modules = [];

		// populate packages
		$.each($("input.requried-module"), function (i, v) {
			packages[i] = $(this).data().package;
			modules[i] = $(this).val();
		});

		disableNextButton();
		var webConfig = $("#setup-step3 form").serializeArray();
		ajaxRequest(
			"/melis/MelisInstaller/Installer/setWebConfig",
			webConfig,
			function (webConfigRes) {
				if (webConfigRes.success !== 1) {
					melisHelper.melisKoNotification(
						translators.tr_melis_installer_platform_modal_title,
						translators.tr_melis_installer_platform_modal_content,
						webConfigRes.errors
					);
				} else {
					$owl.trigger("to.owl.carousel", [nxtPage, 500]);
				}

				// Unchecking Select all checkbox and modules checkboxes
				$("#chkSelectAllModules").prop("checked", false);
				$("#chkSelectAllModules")
					.prev("span")
					.find(".cbmask-inner")
					.removeClass("cb-active");
				$("#frmSelModules")
					.find(".cb-cont input[type=checkbox]")
					.prop("checked", false);
				$("#frmSelModules")
					.find(".cb-cont input[type=checkbox]")
					.prev("span")
					.find(".cbmask-inner")
					.removeClass("cb-active");

				$.each(webConfigRes.requiredModules, function (index, val) {
					$("#frmSelModules")
						.find(".cb-cont input[name=chk" + val + "]")
						.prop("checked", true);
					$("#frmSelModules")
						.find(".cb-cont input[name=chk" + val + "]")
						.addClass("requried-module");
					$("#frmSelModules")
						.find(".cb-cont input[name=chk" + val + "]")
						.prev("span")
						.find(".cbmask-inner")
						.addClass("cb-active");
				});

				melisCoreTool.highlightErrors(
					webConfigRes.success,
					webConfigRes.errors,
					"setup-step3 form"
				);
				enableNextButton();
			}
		);
	}

	function enableNextButton(retainText) {
		$(".setup-pass-page").prop("disabled", false);
		// document.querySelector(".owl-item.active .setup-pass-page").removeAttribute("disabled");
		$(".setup-pass-page").removeClass("btn-default disabled");
		$(".setup-pass-page").addClass("btn-success");

		retainText = retainText || false;

		if (retainText === false) {
			$(".setup-pass-page").html(translators.tr_melis_installer_common_next);
		} else {
			$(".setup-pass-page").html(retainText);
		}
	}

	function disableNextButton() {
		$(".setup-pass-page").prop("disabled", true);
		$(".setup-pass-page").removeClass("btn-success");
		$(".setup-pass-page").addClass("btn-default");
	}

	function lazyNextButton() {
		$(".setup-pass-page").prop("disabled", true);
		$(".setup-pass-page").removeClass("btn-success");
		$(".setup-pass-page").addClass("btn-default");
		$(".setup-pass-page").html(
			'<i class="fa fa-spinner fa-spin"></i> ' +
				translators.melis_installer_common_checking
		);
	}

	function processSelectedModules(nextPage) {
		var dataString = $("#frmSelModules").serializeArray();
		var modDataString = [];
		if (dataString !== null) {
			$.each(dataString, function (i, v) {
				modDataString.push({
					name: v.name,
					value: v.value,
				});
			});
		}
		modDataString.push({
			name: "_default",
			value: "default",
		});

		modDataString = $.param(modDataString);
		ajaxRequest(
			"/melis/MelisInstaller/Installer/addInstallableModules",
			modDataString,
			function (data) {
				$owl.trigger("to.owl.carousel", [nextPage, 500]);
			}
		);
	}

	function getModuleConfiguration(currentPage) {
		getRequest(
			"/melis/MelisInstaller/Installer/getModuleConfigurationForms",
			"html",
			function(data) {
				$("#melis-installer-configuration-forms").html(data);
				//$("i[data-bs-toggle='tooltip']").tooltip();
				$("body").tooltip({ selector: '[data-bs-toggle=tooltip]' });
				$owl.trigger("to.owl.carousel", [currentPage, 500]);
			}
		);
	}

	function submitModuleConfiguration(currentPage) {
		lazyNextButton();
		$(".hasSubmenu:eq( 2 )")
			.find("i")
			.removeClass("fa-circle-o")
			.css("color", "")
			.addClass("fa fa-check fa-color-green");
		var forms = $("#melis-installer-configuration-forms form").serialize();
		getRequest(
			"/melis/MelisInstaller/Installer/validateModuleConfigurationForm?" +
				forms,
			"json",
			function (response) {
				// response.success == "1"
				if (response.success) {
					getRequest(
						"/melis/MelisInstaller/Installer/submitModuleConfigurationForm?" +
							forms,
						"json",
						function (resp) {
							// resp.success == "1"
							if (resp.success) {
								enableNextButton(translators.tr_melis_installer_common_finish);
								$owl.trigger("to.owl.carousel", [currentPage, 500]);
							} else {
								melisInstallerFormHelper.melisMultiKoNotification(
									response.errors
								);
							}
						}
					);
				} else {
					melisInstallerFormHelper.melisMultiKoNotification(response.errors);
					enableNextButton();
				}
			}
		);
	}

	// show modal spinner after you click the finish button
	$body.on("click", ".setup-finish", function () {
		if (isDbTested) {
			disableNextButton();

			$(".setup-finish").html(translators.tr_melis_installer_common_installing);

			$.get("/melis/MelisInstaller/Installer/finalizeSetup", function (data) {
				if (data.success === 1) {
					$(".setup-modal-overlay, .setup-final-modal").fadeIn(300);
					$(".finish-text").show();
					$(".testing-db-text").hide();

					$.ajax({ url: "/melis" }).always(() =>
						setTimeout(function () {
							window.location.replace("/melis");
						}, 5000)
					);
				} else {
					alert(translators.tr_melis_installer_common_finish_error);
				}
			});
		} else {
			$owl.trigger("to.owl.carousel", [5, 500]);
		}
	});

	$body.on("mouseover", ".dropdown-hover", function() {
		$(this).addClass("show");
		$(this).closest(".dropdown").find(".dropdown-menu").addClass("show");
	});

	$body.on("mouseleave", ".dropdown-hover", function() {
		$(this).removeClass("show");
		$(this).closest(".dropdown").find(".dropdown-menu").removeClass("show");
	});

	//if demo cms option is checked
	function showModuleSelect() {
		var webOpt = $("#web-option-selected").val();

		$("#site-install-container").addClass("d-none");
		if (webOpt != "MelisCoreOnly") {
			if (webOpt == "None") {
				$("#weboption-none").trigger("click");
			} else if (webOpt == "NewSite") {
				$("#weboption-newsite").trigger("click");
				$(".setup3-webform").show();
			} else if (webOpt == "MelisDemoCms") {
				$("#weboption-democms").trigger("click");
				$("#site-install-container").removeClass("d-none");
			}
		}
	}

	showModuleSelect();
})(jQuery);

document.addEventListener("DOMContentLoaded", function (event) {
	(function() {
		/* var $body = $("body");
			
			$body.tooltip({ 
				selector: '[data-bs-toggle=tooltip]',
				placement: 'left'
			}); */
		
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl)
		})
	})();
});

function changeSetupLanguage(locale) {
	var datastring = { langLocale: locale };
	// console.log(locale);
	$.ajax({
		type: "POST",
		url: "/melis/MelisInstaller/Installer/changeLang",
		data: datastring,
		dataType: "json",
		encode: true
	}).done(function (data) {
		if (data.success) {
			location.reload();
		}
		// console.log(data);
	});
}
