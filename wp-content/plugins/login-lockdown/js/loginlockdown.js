/**
 * LoginLockdown Pro
 * Admin Functions
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

var LoginLockdown = {};

LoginLockdown.init = function () {};

LoginLockdown.init3rdParty = function ($) {
  $("#loginlockdown_tabs")
    .tabs({
      activate: function (event, ui) {
        window.localStorage.setItem("loginlockdown_tabs", $("#loginlockdown_tabs").tabs("option", "active"));
      },
      create: function (event, ui) {
        if (window.location.hash && $('a[href="' + location.hash + '"]').length) {
          $("#loginlockdown_tabs").tabs(
            "option",
            "active",
            $('a[href="' + location.hash + '"]')
              .parent()
              .index()
          );
        }
      },
      active: window.localStorage.getItem("loginlockdown_tabs"),
    })
    .show();

  // init 2nd level of tabs
  $(".loginlockdown-tabs-2nd-level").each(function () {
    $(this).tabs({
      activate: function (event, ui) {
        window.localStorage.setItem($(this).attr("id"), $(this).tabs("option", "active"));
      },
      active: window.localStorage.getItem($(this).attr("id")),
    });
  });
}; // init3rdParty

LoginLockdown.initUI = function ($) {
  // universal button to close UI dialog in any dialog
  $(".loginlockdown-close-ui-dialog").on("click", function (e) {
    e.preventDefault();

    parent = $(this).closest(".ui-dialog-content");
    $(parent).dialog("close");

    return false;
  }); // close-ui-dialog

  // autosize textareas
  $.each($("#loginlockdown_tabs textarea[data-autoresize]"), function () {
    var offset = this.offsetHeight - this.clientHeight;

    var resizeTextarea = function (el) {
      $(el)
        .css("height", "auto")
        .css("height", el.scrollHeight + offset + 2);
    };
    $(this)
      .on("keyup input click", function () {
        resizeTextarea(this);
      })
      .removeAttr("data-autoresize");
  }); // autosize textareas
}; // initUI

LoginLockdown.fix_dialog_close = function (event, ui) {
  jQuery(".ui-widget-overlay").bind("click", function () {
    jQuery("#" + event.target.id).dialog("close");
  });
}; // fix_dialog_close

LoginLockdown.parse_form_html = function (form_html) {
  var $ = jQuery.noConflict();
  data = {
    action_url: "",
    email_field: "",
    name_field: "",
    extra_data: "",
    method: "",
    email_fields_extra: "",
  };

  html = $.parseHTML('<div id="parse-form-tmp" style="display: none;">' + form_html + "</div>");

  data.action_url = $("form", html).attr("action");
  if ($("form", html).attr("method")) {
    data.method = $("form", html).attr("method").toLowerCase();
  }

  email_fields = $("input[type=email]", html);
  if (email_fields.length == 1) {
    data.email_field = $("input[type=email]", html).attr("name");
  }

  inputs = "";
  $("input", html).each(function (ind, el) {
    type = $(el).attr("type");
    if (type == "email" || type == "button" || type == "reset" || type == "submit") {
      return;
    }

    name = $(el).attr("name");
    name_tmp = name.toLowerCase();

    if (!data.email_field && (name_tmp == "email" || name_tmp == "from" || name_tmp == "emailaddress")) {
      data.email_field = name;
    } else if (name_tmp == "name" || name_tmp == "fname" || name_tmp == "firstname") {
      data.name_field = name;
    } else {
      data.email_fields_extra += name + ", ";
      data.extra_data += name + "=" + $(el).attr("value") + "&";
    }
  }); // foreach

  data.email_fields_extra = data.email_fields_extra.replace(/\, $/g, "");
  data.extra_data = data.extra_data.replace(/&$/g, "");

  return data;
}; // parse_form_html

jQuery(document).ready(function ($) {
  // helper for linking anchors in different tabs
  $(".settings_page_loginlockdown").on("click", ".change_tab", function (e) {
    e.preventDefault();

    tab_name = "loginlockdown_" + $(this).data("tab");
    tab_id = $('#loginlockdown_tabs ul.ui-tabs-nav li[aria-controls="' + tab_name + '"]')
      .attr("aria-labelledby")
      .replace("ui-id-", "");
    if (!tab_id) {
      return false;
    }

    $("#loginlockdown_tabs").tabs("option", "active", tab_id - 1);

    if ($(this).data("tab2")) {
      tab_name2 = "tab_" + $(this).data("tab2");
      tmp = $("#" + tab_name + ' ul.ui-tabs-nav li[aria-controls="' + tab_name2 + '"]');
      tab_id = $("#" + tab_name + " ul.ui-tabs-nav li").index(tmp);
      if (tab_id == -1) {
        return false;
      }

      $("#" + tab_name + " .loginlockdown-tabs-2nd-level").tabs("option", "active", tab_id);
    } // if secondary tab

    // get the link anchor and scroll to it
    target = this.href.split("#")[1];

    return false;
  }); // change tab

  // helper for linking anchors in different tabs
  $(".settings_page_loginlockdown").on("click", ".confirm_action", function (e) {
    message = $(this).data("confirm");

    if (!message || confirm(message)) {
      return true;
    } else {
      e.preventDefault();
      return false;
    }
  }); // confirm action before link click

  $(window).on("hashchange", function () {
    $("#loginlockdown_tabs").tabs(
      "option",
      "active",
      $("a[href=\\" + location.hash + "]")
        .parent()
        .index()
    );
  });

  var selectedTab = getUrlParameter("tab");

  if (selectedTab) {
    $("#loginlockdown_tabs").tabs(
      "option",
      "active",
      $("a[href=\\#" + selectedTab + "]")
        .parent()
        .index()
    );
  }

  LoginLockdown.initUI($);
  LoginLockdown.init3rdParty($);

  $(".settings_page_loginlockdown").on("click", "#deactivate-license", function (e) {
    e.preventDefault();
    button = this;

    wf_loginlockdown_licensing_deactivate_licence_ajax("loginlockdown", $("#license-key").val(), button);
    return;
  });

  // validate license
  $(".settings_page_loginlockdown").on("click", "#save-license", function (e, deactivate) {
    e.preventDefault();
    button = this;
    safe_refresh = true;
    block = block_ui($(button).data("text-wait"));

    wf_loginlockdown_licensing_verify_licence_ajax("lockdown", $("#license-key").val(), button);

    return false;
  }); // validate license

  $("#loginlockdown_keyless_activation").on("click", function (e) {
    e.preventDefault();

    button = this;
    safe_refresh = true;
    block = block_ui($(button).data("text-wait"));

    wf_loginlockdown_licensing_verify_licence_ajax("lockdown", "keyless", button);
    return;
  });

  $("#loginlockdown_deactivate_license").on("click", function (e) {
    e.preventDefault();

    button = this;
    safe_refresh = true;

    wf_loginlockdown_licensing_deactivate_licence_ajax("loginlockdown", $("#license-key").val(), button);
    return;
  });

  // fix for enter press in license field
  $("#license-key").on("keypress", function (e) {
    if (e.which == 13) {
      e.preventDefault();
      $("#save-license").trigger("click");
      return false;
    }
  }); // if enter on license key field

  // open Help Scout Beacon
  $(".settings_page_loginlockdown").on("click", ".open-beacon", function (e) {
    e.preventDefault();
    Beacon("open");
    return false;
  });

  // init Help Scout beacon
  if (loginlockdown_vars.rebranded == false && loginlockdown_vars.whitelabel == true) {
    Beacon("config", {
      enableFabAnimation: false,
      display: {},
      contactForm: {},
      labels: {},
    });
    Beacon("prefill", {
      name: "\n\n\n" + loginlockdown_vars.support_name,
      subject: "Login Lockdown PRO in-plugin support",
      email: "",
      text: "\n\n\n" + loginlockdown_vars.support_text,
    });
    Beacon("init", "d64b767f-176d-4cff-aad6-b1b6ca0ff485");
  }

  // open HS docs and show article based on tool name
  $(".documentation-link").on("click", function (e) {
    e.preventDefault();

    search = $(this).data("tool-title");
    Beacon("search", search);
    Beacon("open");

    return false;
  });

  $("#loginlockdown-locks-log-table").one("preInit.dt", function () {
    $("#loginlockdown-locks-log-table_filter").append('<div id="loginlockdown-locks-log-toggle-chart" title="' + (window.localStorage.getItem("loginlockdown_locks_chart") == "disabled" ? "Show" : "Hide") + ' locks Chart" class="tooltip loginlockdown-locks-log-toggle-chart loginlockdown-locks-log-toggle-chart-' + window.localStorage.getItem("loginlockdown_locks_chart") + '"><i class="loginlockdown-icon loginlockdown-graph"></i></a>');

    $("#loginlockdown-locks-log-table_filter").append('<div id="loginlockdown-locks-log-toggle-stats" title="' + (window.localStorage.getItem("loginlockdown_locks_stats") == "disabled" ? "Show" : "Hide") + ' locks Stats" class="tooltip loginlockdown-locks-log-toggle-stats loginlockdown-locks-log-toggle-stats-' + window.localStorage.getItem("loginlockdown_locks_stats") + '"><i class="loginlockdown-icon loginlockdown-pie"></i></a>');

    $(".tooltip").tooltipster();
  });

  $("#loginlockdown-fails-log-table").one("preInit.dt", function () {
    $("#loginlockdown-fails-log-table_filter").append('<div id="loginlockdown-fails-log-toggle-chart" title="' + (window.localStorage.getItem("loginlockdown_fails_chart") == "disabled" ? "Show" : "Hide") + ' fails Chart" class="tooltip loginlockdown-fails-log-toggle-chart loginlockdown-fails-log-toggle-chart-' + window.localStorage.getItem("loginlockdown_fails_chart") + '"><i class="loginlockdown-icon loginlockdown-graph"></i></a>');
    $("#loginlockdown-fails-log-table_filter").append('<div id="loginlockdown-fails-log-toggle-stats" title="' + (window.localStorage.getItem("loginlockdown_fails_stats") == "disabled" ? "Show" : "Hide") + ' fails Stats" class="tooltip loginlockdown-fails-log-toggle-stats loginlockdown-fails-log-toggle-stats-' + window.localStorage.getItem("loginlockdown_fails_stats") + '"><i class="loginlockdown-icon loginlockdown-pie"></i></a>');

    $(".tooltip").tooltipster();
  });

  $("#loginlockdown_tabs").on("click", ".loginlockdown-fails-log-toggle-chart", function () {
    if ($(this).hasClass("loginlockdown-fails-log-toggle-chart-enabled")) {
      $("#tab_log_full .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-chart-fails").hide(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("full");
          },
        },
        500
      );
      $(this).removeClass("loginlockdown-fails-log-toggle-chart-enabled");
      $(this).addClass("loginlockdown-fails-log-toggle-chart-disabled");
      $(this).attr("title", "Show Failed Attempts Chart");
      window.localStorage.setItem("loginlockdown_fails_chart", "disabled");
    } else {
      $(this).removeClass("loginlockdown-fails-log-toggle-chart-disabled");
      $(this).addClass("loginlockdown-fails-log-toggle-chart-enabled");
      $(this).attr("title", "Hide Failed Attempts Chart");
      window.localStorage.setItem("loginlockdown_fails_chart", "enabled");
      $(".loginlockdown-chart-fails").show();
      create_fails_chart();
      $(".loginlockdown-chart-fails").hide();
      $("#loginlockdown_fails_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-chart-fails").show(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("full");
          },
        },
        500
      );
    }

    $(this).tooltipster("destroy");
    $(".tooltip").tooltipster();
  });

  $("#loginlockdown_tabs").on("click", ".loginlockdown-locks-log-toggle-chart", function () {
    if ($(this).hasClass("loginlockdown-locks-log-toggle-chart-enabled")) {
      $("#tab_log_locks .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-chart-locks").hide(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("locks");
          },
        },
        500
      );
      $(this).removeClass("loginlockdown-locks-log-toggle-chart-enabled");
      $(this).addClass("loginlockdown-locks-log-toggle-chart-disabled");
      $(this).attr("title", "Show Failed Attempts Chart");
      window.localStorage.setItem("loginlockdown_locks_chart", "disabled");
    } else {
      $(this).removeClass("loginlockdown-locks-log-toggle-chart-disabled");
      $(this).addClass("loginlockdown-locks-log-toggle-chart-enabled");
      $(this).attr("title", "Hide Lockdowns Chart");
      window.localStorage.setItem("loginlockdown_locks_chart", "enabled");
      $(".loginlockdown-chart-locks").show();
      create_locks_chart();
      $(".loginlockdown-chart-locks").hide();
      $("#loginlockdown_locks_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-chart-locks").show(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("locks");
          },
        },
        500
      );
    }

    $(this).tooltipster("destroy");
    $(".tooltip").tooltipster();
  });

  $("body")
  .on("input", 'input[type="range"]', function (e) {
    $(this).parents("td").find(".range_value").html(this.value);
  })
  .trigger("change");

  function center_locks_placeholder(type) {
    var placeholder_top = 0;

    if ($("#tab_log_" + type + " .loginlockdown-chart-" + type + "").is(":visible")) {
      placeholder_top = placeholder_top + 70;
    }
    if ($("#tab_log_" + type + " .loginlockdown-stats-" + type + "").is(":visible")) {
      placeholder_top = placeholder_top + 120;
    }

    $("#tab_log_" + type + " .loginlockdown-chart-placeholder").css("top", placeholder_top + "px");
    if (placeholder_top == 0) {
      $("#tab_log_" + type + " .loginlockdown-chart-placeholder").hide();
    } else {
      $("#tab_log_" + type + " .loginlockdown-chart-placeholder").fadeIn(300);
      $("#tab_log_" + type + " .loginlockdown-chart-placeholder").css("top", placeholder_top + "px");
    }
  }

  if (loginlockdown_vars.stats_locks.total == 0) {
    var placeholder_top = 0;
    if (window.localStorage.getItem("loginlockdown_locks_stats") == "enabled") {
      placeholder_top = placeholder_top + 70;
    }
    if (window.localStorage.getItem("loginlockdown_locks_chart") == "enabled") {
      placeholder_top = placeholder_top + 120;
    }
    $(".loginlockdown-chart-locks").css("filter", "blur(3px)");
    $(".loginlockdown-stats-locks").css("filter", "blur(3px)");
    $("#tab_log_locks").append('<div class="loginlockdown-chart-placeholder">' + loginlockdown_vars.stats_unavailable + "</div>");

    if (placeholder_top == 0) {
      $("#tab_log_locks .loginlockdown-chart-placeholder").hide();
    } else {
      $("#tab_log_locks .loginlockdown-chart-placeholder").css("top", placeholder_top + "px");
      $("#loginlockdown_locks_log .loginlockdown-chart-placeholder").fadeIn(300);
    }
  }

  if (loginlockdown_vars.stats_fails.total == 0) {
    var placeholder_top = 0;
    if (window.localStorage.getItem("loginlockdown_fails_stats") == "enabled") {
      placeholder_top = placeholder_top + 70;
    }
    if (window.localStorage.getItem("loginlockdown_fails_chart") == "enabled") {
      placeholder_top = placeholder_top + 120;
    }
    $(".loginlockdown-chart-fails").css("filter", "blur(3px)");
    $(".loginlockdown-stats-fails").css("filter", "blur(3px)");
    $("#tab_log_full").append('<div class="loginlockdown-chart-placeholder">' + loginlockdown_vars.stats_unavailable + "</div>");

    if (placeholder_top == 0) {
      $("#tab_log_full .loginlockdown-chart-placeholder").hide();
    } else {
      $("#tab_log_full .loginlockdown-chart-placeholder").css("top", placeholder_top + "px");
      $("#loginlockdown_fails_log .loginlockdown-chart-placeholder").fadeIn(300);
    }
  }

  $("#loginlockdown_tabs").on("click", ".loginlockdown-fails-log-toggle-stats", function () {
    if ($(this).hasClass("loginlockdown-fails-log-toggle-stats-enabled")) {
      $("#loginlockdown_fails_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-stats-fails").hide(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("full");
          },
        },
        500
      );
      $(this).removeClass("loginlockdown-fails-log-toggle-stats-enabled");
      $(this).addClass("loginlockdown-fails-log-toggle-stats-disabled");
      $(this).attr("title", "Show Failed Attempts Stats");
      window.localStorage.setItem("loginlockdown_fails_stats", "disabled");
    } else {
      $(this).removeClass("loginlockdown-fails-log-toggle-stats-disabled");
      $(this).addClass("loginlockdown-fails-log-toggle-stats-enabled");
      $(this).attr("title", "Hide fails Stats");
      create_fails_device_chart();
      window.localStorage.setItem("loginlockdown_fails_stats", "enabled");
      $(".loginlockdown-stats-fails").show();
      $(".loginlockdown-stats-fails").hide();
      $("#loginlockdown_fails_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-stats-fails").show(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("full");
          },
        },
        500
      );
    }

    $(this).tooltipster("destroy");
    $(".tooltip").tooltipster();
  });

  $("#loginlockdown_tabs").on("click", ".loginlockdown-locks-log-toggle-stats", function () {
    if ($(this).hasClass("loginlockdown-locks-log-toggle-stats-enabled")) {
      $("#loginlockdown_locks_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-stats-locks").hide(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("locks");
          },
        },
        500
      );
      $(this).removeClass("loginlockdown-locks-log-toggle-stats-enabled");
      $(this).addClass("loginlockdown-locks-log-toggle-stats-disabled");
      $(this).attr("title", "Show Lockdowns Stats");
      window.localStorage.setItem("loginlockdown_locks_stats", "disabled");
    } else {
      $(this).removeClass("loginlockdown-locks-log-toggle-stats-disabled");
      $(this).addClass("loginlockdown-locks-log-toggle-stats-enabled");
      $(this).attr("title", "Hide Lockdowns Stats");
      create_locks_device_chart();
      window.localStorage.setItem("loginlockdown_locks_stats", "enabled");
      $(".loginlockdown-stats-locks").show();
      $(".loginlockdown-stats-locks").hide();
      $("#loginlockdown_locks_log .loginlockdown-chart-placeholder").fadeOut(300);
      $(".loginlockdown-stats-locks").show(
        "blind",
        {
          direction: "vertical",
          complete: function () {
            center_locks_placeholder("locks");
          },
        },
        500
      );
    }

    $(this).tooltipster("destroy");
    $(".tooltip").tooltipster();
  });

  $(".settings_page_loginlockdown").on("click", ".unlock_lockdown", function (e) {
    e.preventDefault();
    $.post({
      url: ajaxurl,
      data: {
        action: "loginlockdown_run_tool",
        _ajax_nonce: loginlockdown_vars.run_tool_nonce,
        tool: "unlock_lockdown",
        lock_id: $(this).data("lock-id"),
      },
    })
      .always(function (response) {})
      .done(function (response) {
        location.reload();
      });
  });

  $(".settings_page_loginlockdown").on("click", ".delete_lock_entry", function (e) {
    e.preventDefault();
    uid = $(this).data("lock-uid");
    button = $(this);

    loginlockdown_swal
      .fire({
        title: $(button).data("title"),
        type: "question",
        text: $(button).data("text"),
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: $(button).data("btn-confirm"),
        cancelButtonText: loginlockdown_vars.cancel_button,
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          block = block_ui($(button).data("msg-wait"));
          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "delete_lock_log",
              lock_id: $(button).data("lock-id"),
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
            })
            .done(function (response) {
              if (response.success) {
                $("#loginlockdown-locks-log-table tr#" + response.data.id).remove();
                loginlockdown_swal.fire({
                  type: "success",
                  heightAuto: false,
                  title: $(button).data("msg-success"),
                });
              } else {
                loginlockdown_swal.fire({
                  type: "error",
                  heightAuto: false,
                  title: loginlockdown_vars.documented_error + " " + data.data,
                });
              }
            })
            .fail(function (response) {
              loginlockdown_swal.fire({
                type: "error",
                heightAuto: false,
                title: loginlockdown_vars.undocumented_error,
              });
            });
        } // if confirmed
      });
  });

  $(".settings_page_loginlockdown").on("click", ".empty_log", function (e) {
    e.preventDefault();
    button = $(this);

    loginlockdown_swal
      .fire({
        title: $(button).data("title"),
        type: "question",
        text: $(button).data("text"),
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: $(button).data("btn-confirm"),
        cancelButtonText: loginlockdown_vars.cancel_button,
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          block = block_ui($(button).data("msg-wait"));
          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "empty_log",
              log: $(button).data("log"),
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
            })
            .done(function (response) {
              location.reload();
            })
            .fail(function (response) {
              loginlockdown_swal.fire({
                type: "error",
                heightAuto: false,
                title: loginlockdown_vars.undocumented_error,
              });
            });
        } // if confirmed
      });
  });

  $("#toggle_firewall_rules").on('change', function(){
    $( ".firewall_rule_toggle" ).prop( "checked", $(this).is(':checked') );
    $( ".firewall_rule_toggle" ).trigger("change");
  });

  jQuery(document).ready(function($){
    $('.lockdown-color').wpColorPicker();
  });

  $(".settings_page_loginlockdown").on("click", ".captcha-box-wrapper img", function (e) {
    $("#captcha").val($(this).parent().data("captcha"));
    $("#captcha").trigger("change");
    $(".captcha-box-wrapper").removeClass("captcha-selected");
    $(this).parent().addClass("captcha-selected");
  });

  $(".settings_page_loginlockdown").on("blur change keyup", "#captcha,#captcha_site_key,#captcha_secret_key", function (e) {
    if(($('#captcha').val() == 'recaptchav2' || $('#captcha').val() == 'recaptchav3' || $('#captcha').val() == 'hcaptcha') && $(this).val() != $(this).data('old')){
        $('.captcha_verify_wrapper').show();
    } else {
        $('.captcha_verify_wrapper').hide();
    }
  });

  $(".settings_page_loginlockdown").on("click", "#verify-captcha", function (e) {
    e.preventDefault();
    var captcha_response;

    loginlockdown_swal
      .fire({
        title: 'Verify Captcha Keys',
        type: "",
        icon: "",
        html: '<div class="loginlockdown-swal-captcha-wrapper"><div class="loginlockdown-captcha-loader"><img width="64" src="' + loginlockdown_vars.icon_url + '" /></div><div id="loginlockdown_captcha_box" style="margin: 0 auto; display: inline-block;"></div></div>',
        onOpen: () => {
            window.loginlockdown_captcha_script = document.createElement('script');
            if($('#captcha').val() == 'recaptchav2'){
                window.loginlockdown_captcha_script.src = 'https://www.google.com/recaptcha/api.js?onload=loginlockdown_captchav2_test&render=explicit';
            }

            if($('#captcha').val() == 'recaptchav3'){
                window.loginlockdown_captcha_script.src = 'https://www.google.com/recaptcha/api.js?onload=loginlockdown_captchav3_test&render=' + $('#captcha_site_key').val();
            }

            if($('#captcha').val() == 'hcaptcha'){
                window.loginlockdown_captcha_script.src = 'https://www.hCaptcha.com/1/api.js?render=explicit';

                window.loginlockdown_captcha_script.onload = function () {
                    $('.loginlockdown-captcha-loader').remove();
                     window.loginlockdown_captcha_box = hcaptcha.render('loginlockdown_captcha_box', {
                        sitekey : $('#captcha_site_key').val(),
                        theme : 'light',
                        callback : () => {
                            captcha_response = hcaptcha.getResponse(window.loginlockdown_captcha_box);
                        }
                    });
                }
            }



            window.loginlockdown_captcha_script.onerror = function () {
                loginlockdown_swal.close();
                loginlockdown_swal.fire({
                    type: "error",
                    heightAuto: false,
                    title: 'An error occured loading the captcha, please check your Captcha Site Key',
                });
            };

            window.loginlockdown_captchav2_test = function(){
                $('.loginlockdown-captcha-loader').remove();
                window.loginlockdown_captcha_box = grecaptcha.render('loginlockdown_captcha_box', {
                    'sitekey' : $('#captcha_site_key').val(),
                    'theme' : 'light',
                    'callback' : () => {
                        captcha_response = grecaptcha.getResponse(window.loginlockdown_captcha_box);
                    }
                });
            }

            window.loginlockdown_captchav3_test = function(){
                grecaptcha.execute($('#captcha_site_key').val(), {action: 'submit'}).then(function(token) {
                    $('.loginlockdown-swal-captcha-wrapper').html();
                    captcha_response = token;
                    $('.loginlockdown-swal-captcha-wrapper').html('Captcha token ready, click Submit Captcha to verify it');
                });
            }

            window.loginlockdown_hcaptcha_test = function(){
                $('.loginlockdown-captcha-loader').remove();
                window.loginlockdown_captcha_box = hcaptcha.render('loginlockdown_captcha_box', {
                    sitekey : $('#captcha_site_key').val(),
                    theme : 'light',
                    callback : () => {
                        captcha_response = hcaptcha.getResponse(window.loginlockdown_captcha_box);
                    }
                });
            }

            document.head.appendChild(window.loginlockdown_captcha_script);
        },
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: 'Submit Captcha',
        cancelButtonText: 'Cancel',
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          block = block_ui('Verifying captcha');

          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "verify_captcha",
              captcha_type: $('#captcha').val(),
              captcha_site_key: $('#captcha_site_key').val(),
              captcha_secret_key: $('#captcha_secret_key').val(),
              captcha_response: captcha_response,
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
              document.head.removeChild(window.loginlockdown_captcha_script);
              window.loginlockdown_captcha_script = null;
              window.loginlockdown_captchav2_test = null;
              window.loginlockdown_captchav3_test = null;
              window.loginlockdown_hcaptcha_test = null;

            })
            .done(function (response) {
                if(response.success){
                    $('#captcha_site_key').data('old', $('#captcha_site_key').val());
                    $('#captcha_secret_key').data('old', $('#captcha_secret_key').val());
                    $('.captcha_verify_wrapper').hide();
                    $('#captcha_verified').val('1');
                    loginlockdown_swal.fire({
                        type: "success",
                        heightAuto: false,
                        title: 'Captcha has been verified successfully',
                    });
                } else {
                    loginlockdown_swal.fire({
                        type: "error",
                        heightAuto: false,
                        title: response.data,
                    });
                }
            })
            .fail(function (response) {
              loginlockdown_swal.fire({
                type: "error",
                heightAuto: false,
                title: loginlockdown_vars.undocumented_error,
              });
            });
        } // if confirmed
      });
  });


  $(".settings_page_loginlockdown").on("click", ".delete_failed_entry", function (e) {
    e.preventDefault();
    uid = $(this).data("failed-uid");
    button = $(this);

    loginlockdown_swal
      .fire({
        title: $(button).data("title"),
        type: "question",
        text: $(button).data("text"),
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: $(button).data("btn-confirm"),
        cancelButtonText: loginlockdown_vars.cancel_button,
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          block = block_ui($(button).data("msg-wait"));
          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "delete_fail_log",
              fail_id: $(button).data("failed-id"),
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
            })
            .done(function (response) {
              if (response.success) {
                $("#loginlockdown-fails-log-table tr#" + response.data.id).remove();
                loginlockdown_swal.fire({
                  type: "success",
                  heightAuto: false,
                  title: $(button).data("msg-success"),
                });
              } else {
                loginlockdown_swal.fire({
                  type: "error",
                  heightAuto: false,
                  title: loginlockdown_vars.documented_error + " " + data.data,
                });
              }
            })
            .fail(function (response) {
              loginlockdown_swal.fire({
                type: "error",
                heightAuto: false,
                title: loginlockdown_vars.undocumented_error,
              });
            });
        } // if confirmed
      });
  });

  // display a message while an action is performed
  function block_ui(message) {
    tmp = loginlockdown_swal.fire({
      text: message,
      type: false,
      imageUrl: loginlockdown_vars.icon_url,
      onOpen: () => {},
      imageWidth: 58,
      imageHeight: 58,
      imageAlt: message,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      heightAuto: false,
    });

    return tmp;
  } // block_ui

  function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
      sURLVariables = sPageURL.split("&"),
      sParameterName,
      i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split("=");

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
      }
    }
  }

  table_locks_logs = $("#loginlockdown-locks-log-table").dataTable({
    bProcessing: true,
    bServerSide: true,
    bLengthChange: 1,
    bProcessing: true,
    bStateSave: 0,
    bAutoWidth: 0,
    columnDefs: [
      {
        targets: [1],
        className: "dt-body-center",
        orderable: false,
      },
      {
        targets: [2],
        className: "dt-body-center",
        orderable: false,
      },
      {
        targets: [3],
        className: "dt-body-center",
        orderable: false,
      },
      {
        targets: [4],
        className: "dt-body-center",
        orderable: false,
      },
      {
        targets: [5],
        className: "dt-body-right",
        orderable: false,
      },
    ],
    drawCallback: function () {
      $(".tooltip").tooltipster();
    },
    initComplete: function () {
      $(".tooltip").tooltipster();
    },
    language: {
      loadingRecords: "&nbsp;",
      processing: '<div class="loginlockdown-datatables-loader"><img width="64" src="' + loginlockdown_vars.icon_url + '" /></div>',
      emptyTable: "No Lockdowns exist yet",
      searchPlaceholder: "Type something to search ...",
      search: "",
    },
    order: [[0, "desc"]],
    iDisplayLength: 25,
    sPaginationType: "full_numbers",
    dom: '<"settings_page_loginlockdown_top"f>rt<"bottom"lp><"clear">',
    sAjaxSource: ajaxurl + "?action=loginlockdown_run_tool&tool=locks_logs&_ajax_nonce=" + loginlockdown_vars.run_tool_nonce,
  });

  table_activity_logs = $("#loginlockdown-fails-log-table").dataTable({
    bProcessing: true,
    bServerSide: true,
    bLengthChange: 1,
    bProcessing: true,
    bStateSave: 0,
    bAutoWidth: 0,
    columnDefs: [
      {
        targets: [3],
        className: "dt-body-center",
        orderable: false,
      },
      {
        targets: [4],
        className: "dt-body-right",
        orderable: false,
      },
    ],
    drawCallback: function () {
      $(".tooltip").tooltipster();
    },
    initComplete: function () {
      $(".tooltip").tooltipster();
    },
    language: {
      loadingRecords: "&nbsp;",
      processing: '<div class="loginlockdown-datatables-loader"><img width="64" src="' + loginlockdown_vars.icon_url + '" /></div>',
      emptyTable: "No failed attempts exist yet",
      searchPlaceholder: "Type something to search ...",
      search: "",
    },
    order: [[0, "desc"]],
    iDisplayLength: 25,
    sPaginationType: "full_numbers",
    dom: '<"settings_page_loginlockdown_top"f>rt<"bottom"lp><"clear">',
    sAjaxSource: ajaxurl + "?action=loginlockdown_run_tool&tool=activity_logs&_ajax_nonce=" + loginlockdown_vars.run_tool_nonce,
  });

  if ($("#captcha").val() != "disabled" && $("#captcha").val() != "builtin") {
    $(".captcha_keys_wrapper").show();
  } else {
    $(".captcha_keys_wrapper").hide();
  }

  $("#captcha").on("change", function () {
    if ($("#captcha").val() != "disabled" && $("#captcha").val() != "builtin") {
      $(".captcha_keys_wrapper").show();
    } else {
      $(".captcha_keys_wrapper").hide();
    }
  });

  if ($("#country_blocking_mode").val() != "none") {
    $(".country-blocking-wrapper").show();
    if ($("#country_blocking_mode").val() == "whitelist") {
      $(".country-blocking-label").html("Allowed Countries");
    } else {
      $(".country-blocking-label").html("Blocked Countries");
    }
  } else {
    $(".country-blocking-wrapper").hide();
  }

  $("#country_blocking_mode").on("change", function () {
    if ($("#country_blocking_mode").val() != "none") {
      $(".country-blocking-wrapper").show();
      if ($("#country_blocking_mode").val() == "whitelist") {
        $(".country-blocking-label").html("Allowed Countries");
      } else {
        $(".country-blocking-label").html("Blocked Countries");
      }
    } else {
      $(".country-blocking-wrapper").hide();
    }
  });

  var isoCountries = [
    { id: "AF", text: "Afghanistan" },
    { id: "AX", text: "Aland Islands" },
    { id: "AL", text: "Albania" },
    { id: "DZ", text: "Algeria" },
    { id: "AS", text: "American Samoa" },
    { id: "AD", text: "Andorra" },
    { id: "AO", text: "Angola" },
    { id: "AI", text: "Anguilla" },
    { id: "AQ", text: "Antarctica" },
    { id: "AG", text: "Antigua And Barbuda" },
    { id: "AR", text: "Argentina" },
    { id: "AM", text: "Armenia" },
    { id: "AW", text: "Aruba" },
    { id: "AU", text: "Australia" },
    { id: "AT", text: "Austria" },
    { id: "AZ", text: "Azerbaijan" },
    { id: "BS", text: "Bahamas" },
    { id: "BH", text: "Bahrain" },
    { id: "BD", text: "Bangladesh" },
    { id: "BB", text: "Barbados" },
    { id: "BY", text: "Belarus" },
    { id: "BE", text: "Belgium" },
    { id: "BZ", text: "Belize" },
    { id: "BJ", text: "Benin" },
    { id: "BM", text: "Bermuda" },
    { id: "BT", text: "Bhutan" },
    { id: "BO", text: "Bolivia" },
    { id: "BA", text: "Bosnia And Herzegovina" },
    { id: "BW", text: "Botswana" },
    { id: "BV", text: "Bouvet Island" },
    { id: "BR", text: "Brazil" },
    { id: "IO", text: "British Indian Ocean Territory" },
    { id: "BN", text: "Brunei Darussalam" },
    { id: "BG", text: "Bulgaria" },
    { id: "BF", text: "Burkina Faso" },
    { id: "BI", text: "Burundi" },
    { id: "KH", text: "Cambodia" },
    { id: "CM", text: "Cameroon" },
    { id: "CA", text: "Canada" },
    { id: "CV", text: "Cape Verde" },
    { id: "KY", text: "Cayman Islands" },
    { id: "CF", text: "Central African Republic" },
    { id: "TD", text: "Chad" },
    { id: "CL", text: "Chile" },
    { id: "CN", text: "China" },
    { id: "CX", text: "Christmas Island" },
    { id: "CC", text: "Cocos (Keeling) Islands" },
    { id: "CO", text: "Colombia" },
    { id: "KM", text: "Comoros" },
    { id: "CG", text: "Congo" },
    { id: "CD", text: "Congo}, Democratic Republic" },
    { id: "CK", text: "Cook Islands" },
    { id: "CR", text: "Costa Rica" },
    { id: "CI", text: "Cote D'Ivoire" },
    { id: "HR", text: "Croatia" },
    { id: "CU", text: "Cuba" },
    { id: "CY", text: "Cyprus" },
    { id: "CZ", text: "Czech Republic" },
    { id: "DK", text: "Denmark" },
    { id: "DJ", text: "Djibouti" },
    { id: "DM", text: "Dominica" },
    { id: "DO", text: "Dominican Republic" },
    { id: "EC", text: "Ecuador" },
    { id: "EG", text: "Egypt" },
    { id: "SV", text: "El Salvador" },
    { id: "GQ", text: "Equatorial Guinea" },
    { id: "ER", text: "Eritrea" },
    { id: "EE", text: "Estonia" },
    { id: "ET", text: "Ethiopia" },
    { id: "FK", text: "Falkland Islands (Malvinas)" },
    { id: "FO", text: "Faroe Islands" },
    { id: "FJ", text: "Fiji" },
    { id: "FI", text: "Finland" },
    { id: "FR", text: "France" },
    { id: "GF", text: "French Guiana" },
    { id: "PF", text: "French Polynesia" },
    { id: "TF", text: "French Southern Territories" },
    { id: "GA", text: "Gabon" },
    { id: "GM", text: "Gambia" },
    { id: "GE", text: "Georgia" },
    { id: "DE", text: "Germany" },
    { id: "GH", text: "Ghana" },
    { id: "GI", text: "Gibraltar" },
    { id: "GR", text: "Greece" },
    { id: "GL", text: "Greenland" },
    { id: "GD", text: "Grenada" },
    { id: "GP", text: "Guadeloupe" },
    { id: "GU", text: "Guam" },
    { id: "GT", text: "Guatemala" },
    { id: "GG", text: "Guernsey" },
    { id: "GN", text: "Guinea" },
    { id: "GW", text: "Guinea-Bissau" },
    { id: "GY", text: "Guyana" },
    { id: "HT", text: "Haiti" },
    { id: "HM", text: "Heard Island & Mcdonald Islands" },
    { id: "VA", text: "Holy See (Vatican City State)" },
    { id: "HN", text: "Honduras" },
    { id: "HK", text: "Hong Kong" },
    { id: "HU", text: "Hungary" },
    { id: "IS", text: "Iceland" },
    { id: "IN", text: "India" },
    { id: "ID", text: "Indonesia" },
    { id: "IR", text: "Iran}, Islamic Republic Of" },
    { id: "IQ", text: "Iraq" },
    { id: "IE", text: "Ireland" },
    { id: "IM", text: "Isle Of Man" },
    { id: "IL", text: "Israel" },
    { id: "IT", text: "Italy" },
    { id: "JM", text: "Jamaica" },
    { id: "JP", text: "Japan" },
    { id: "JE", text: "Jersey" },
    { id: "JO", text: "Jordan" },
    { id: "KZ", text: "Kazakhstan" },
    { id: "KE", text: "Kenya" },
    { id: "KI", text: "Kiribati" },
    { id: "KR", text: "Korea" },
    { id: "KW", text: "Kuwait" },
    { id: "KG", text: "Kyrgyzstan" },
    { id: "LA", text: "Lao People's Democratic Republic" },
    { id: "LV", text: "Latvia" },
    { id: "LB", text: "Lebanon" },
    { id: "LS", text: "Lesotho" },
    { id: "LR", text: "Liberia" },
    { id: "LY", text: "Libyan Arab Jamahiriya" },
    { id: "LI", text: "Liechtenstein" },
    { id: "LT", text: "Lithuania" },
    { id: "LU", text: "Luxembourg" },
    { id: "MO", text: "Macao" },
    { id: "MK", text: "Macedonia" },
    { id: "MG", text: "Madagascar" },
    { id: "MW", text: "Malawi" },
    { id: "MY", text: "Malaysia" },
    { id: "MV", text: "Maldives" },
    { id: "ML", text: "Mali" },
    { id: "MT", text: "Malta" },
    { id: "MH", text: "Marshall Islands" },
    { id: "MQ", text: "Martinique" },
    { id: "MR", text: "Mauritania" },
    { id: "MU", text: "Mauritius" },
    { id: "YT", text: "Mayotte" },
    { id: "MX", text: "Mexico" },
    { id: "FM", text: "Micronesia}, Federated States Of" },
    { id: "MD", text: "Moldova" },
    { id: "MC", text: "Monaco" },
    { id: "MN", text: "Mongolia" },
    { id: "ME", text: "Montenegro" },
    { id: "MS", text: "Montserrat" },
    { id: "MA", text: "Morocco" },
    { id: "MZ", text: "Mozambique" },
    { id: "MM", text: "Myanmar" },
    { id: "NA", text: "Namibia" },
    { id: "NR", text: "Nauru" },
    { id: "NP", text: "Nepal" },
    { id: "NL", text: "Netherlands" },
    { id: "AN", text: "Netherlands Antilles" },
    { id: "NC", text: "New Caledonia" },
    { id: "NZ", text: "New Zealand" },
    { id: "NI", text: "Nicaragua" },
    { id: "NE", text: "Niger" },
    { id: "NG", text: "Nigeria" },
    { id: "NU", text: "Niue" },
    { id: "NF", text: "Norfolk Island" },
    { id: "MP", text: "Northern Mariana Islands" },
    { id: "NO", text: "Norway" },
    { id: "OM", text: "Oman" },
    { id: "PK", text: "Pakistan" },
    { id: "PW", text: "Palau" },
    { id: "PS", text: "Palestinian Territory}, Occupied" },
    { id: "PA", text: "Panama" },
    { id: "PG", text: "Papua New Guinea" },
    { id: "PY", text: "Paraguay" },
    { id: "PE", text: "Peru" },
    { id: "PH", text: "Philippines" },
    { id: "PN", text: "Pitcairn" },
    { id: "PL", text: "Poland" },
    { id: "PT", text: "Portugal" },
    { id: "PR", text: "Puerto Rico" },
    { id: "QA", text: "Qatar" },
    { id: "RE", text: "Reunion" },
    { id: "RO", text: "Romania" },
    { id: "RU", text: "Russian Federation" },
    { id: "RW", text: "Rwanda" },
    { id: "BL", text: "Saint Barthelemy" },
    { id: "SH", text: "Saint Helena" },
    { id: "KN", text: "Saint Kitts And Nevis" },
    { id: "LC", text: "Saint Lucia" },
    { id: "MF", text: "Saint Martin" },
    { id: "PM", text: "Saint Pierre And Miquelon" },
    { id: "VC", text: "Saint Vincent And Grenadines" },
    { id: "WS", text: "Samoa" },
    { id: "SM", text: "San Marino" },
    { id: "ST", text: "Sao Tome And Principe" },
    { id: "SA", text: "Saudi Arabia" },
    { id: "SN", text: "Senegal" },
    { id: "RS", text: "Serbia" },
    { id: "SC", text: "Seychelles" },
    { id: "SL", text: "Sierra Leone" },
    { id: "SG", text: "Singapore" },
    { id: "SK", text: "Slovakia" },
    { id: "SI", text: "Slovenia" },
    { id: "SB", text: "Solomon Islands" },
    { id: "SO", text: "Somalia" },
    { id: "ZA", text: "South Africa" },
    { id: "GS", text: "South Georgia And Sandwich Isl." },
    { id: "ES", text: "Spain" },
    { id: "LK", text: "Sri Lanka" },
    { id: "SD", text: "Sudan" },
    { id: "SR", text: "Suriname" },
    { id: "SJ", text: "Svalbard And Jan Mayen" },
    { id: "SZ", text: "Swaziland" },
    { id: "SE", text: "Sweden" },
    { id: "CH", text: "Switzerland" },
    { id: "SY", text: "Syrian Arab Republic" },
    { id: "TW", text: "Taiwan" },
    { id: "TJ", text: "Tajikistan" },
    { id: "TZ", text: "Tanzania" },
    { id: "TH", text: "Thailand" },
    { id: "TL", text: "Timor-Leste" },
    { id: "TG", text: "Togo" },
    { id: "TK", text: "Tokelau" },
    { id: "TO", text: "Tonga" },
    { id: "TT", text: "Trinidad And Tobago" },
    { id: "TN", text: "Tunisia" },
    { id: "TR", text: "Turkey" },
    { id: "TM", text: "Turkmenistan" },
    { id: "TC", text: "Turks And Caicos Islands" },
    { id: "TV", text: "Tuvalu" },
    { id: "UG", text: "Uganda" },
    { id: "UA", text: "Ukraine" },
    { id: "AE", text: "United Arab Emirates" },
    { id: "GB", text: "United Kingdom" },
    { id: "US", text: "United States" },
    { id: "UM", text: "United States Outlying Islands" },
    { id: "UY", text: "Uruguay" },
    { id: "UZ", text: "Uzbekistan" },
    { id: "VU", text: "Vanuatu" },
    { id: "VE", text: "Venezuela" },
    { id: "VN", text: "Viet Nam" },
    { id: "VG", text: "Virgin Islands}, British" },
    { id: "VI", text: "Virgin Islands}, U.S." },
    { id: "WF", text: "Wallis And Futuna" },
    { id: "EH", text: "Western Sahara" },
    { id: "YE", text: "Yemen" },
    { id: "ZM", text: "Zambia" },
    { id: "ZW", text: "Zimbabwe" },
  ];

  function formatCountry(country) {
    if (!country.id) {
      return country.text;
    }

    var $country = $('<span class="country-flag"><img src="' + loginlockdown_vars.plugin_url + "/images/flags/" + country.id.toLowerCase() + '.png" /><span class="flag-text">' + country.text + "</span>");
    return $country;
  }

  if ($("#country_blocking_countries").length) {
    $("#country_blocking_countries").select2({
      placeholder: "Select countries",
      templateResult: formatCountry,
      data: isoCountries,
      width: "resolve",
    });

    $("#country_blocking_countries").val($("#country_blocking_countries").data("countries").split(",")).change();
  }

  Chart.defaults.global.defaultFontColor = "#23282d";
  Chart.defaults.global.defaultFontFamily = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif';
  Chart.defaults.global.defaultFontSize = 12;
  var loginlockdown_fails_chart;
  var loginlockdown_locks_chart;
  var loginlockdown_fails_device_chart;
  var loginlockdown_locks_device_chart;

  function create_locks_chart() {
    if (!loginlockdown_vars.stats_locks || !loginlockdown_vars.stats_locks.days.length) {
      $("#loginlockdown-locks-chart").remove();
      return;
    } else {
      if (loginlockdown_locks_chart) {
        loginlockdown_locks_chart.destroy();
      }

      var chartlockscanvas = document.getElementById("loginlockdown-locks-chart").getContext("2d");
      var gradient = chartlockscanvas.createLinearGradient(0, 0, 0, 200);
      gradient.addColorStop(0, "#f9f9f9");
      gradient.addColorStop(1, "#ffffff");

      loginlockdown_locks_chart = new Chart(chartlockscanvas, {
        type: "line",

        data: {
          labels: loginlockdown_vars.stats_locks.days,
          datasets: [
            {
              label: "Locks",
              yAxisID: "yleft",
              xAxisID: "xdown",
              data: loginlockdown_vars.stats_locks.count,
              backgroundColor: gradient,
              borderColor: loginlockdown_vars.chart_colors[0],
              hoverBackgroundColor: loginlockdown_vars.chart_colors[0],
              borderWidth: 0,
            },
          ],
        },
        options: {
          animation: false,
          legend: false,
          maintainAspectRatio: false,
          tooltips: {
            mode: "index",
            intersect: false,
            callbacks: {
              title: function (value, values) {
                index = value[0].index;
                return moment(values.labels[index], "YYYY-MM-DD").format("dddd, MMMM Do");
              },
            },
            displayColors: false,
          },
          scales: {
            xAxes: [
              {
                display: false,
                id: "xdown",
                stacked: true,
                ticks: {
                  callback: function (value, index, values) {
                    return moment(value, "YYYY-MM-DD").format("MMM Do");
                  },
                },
                categoryPercentage: 0.85,
                time: {
                  unit: "day",
                  displayFormats: { day: "MMM Do" },
                  tooltipFormat: "dddd, MMMM Do",
                },
                gridLines: { display: false },
              },
            ],
            yAxes: [
              {
                display: false,
                id: "yleft",
                position: "left",
                type: "linear",
                scaleLabel: {
                  display: true,
                  labelString: "Hits",
                },
                gridLines: { display: false },
                stacked: false,
                ticks: {
                  beginAtZero: false,
                  maxTicksLimit: 12,
                  callback: function (value, index, values) {
                    return Math.round(value);
                  },
                },
              },
            ],
          },
        },
      });
    }
  }

  function create_fails_chart() {
    if (!loginlockdown_vars.stats_fails || !loginlockdown_vars.stats_fails.days.length) {
      $("#loginlockdown-fails-chart").remove();
      return;
    } else {
      if (loginlockdown_fails_chart) loginlockdown_fails_chart.destroy();

      var chartfailscanvas = document.getElementById("loginlockdown-fails-chart").getContext("2d");
      var gradient = chartfailscanvas.createLinearGradient(0, 0, 0, 200);
      gradient.addColorStop(0, "#f9f9f9");
      gradient.addColorStop(1, "#ffffff");

      loginlockdown_fails_chart = new Chart(chartfailscanvas, {
        type: "line",
        data: {
          labels: loginlockdown_vars.stats_fails.days,
          datasets: [
            {
              label: "Fails",
              yAxisID: "yleft",
              xAxisID: "xdown",
              data: loginlockdown_vars.stats_fails.count,
              backgroundColor: gradient,
              borderColor: loginlockdown_vars.chart_colors[0],
              hoverBackgroundColor: loginlockdown_vars.chart_colors[0],
              borderWidth: 0,
            },
          ],
        },
        options: {
          animation: false,
          legend: false,
          maintainAspectRatio: false,
          tooltips: {
            mode: "index",
            intersect: false,
            callbacks: {
              title: function (value, values) {
                index = value[0].index;
                return moment(values.labels[index], "YYYY-MM-DD").format("dddd, MMMM Do");
              },
            },
            displayColors: false,
          },

          scales: {
            xAxes: [
              {
                display: false,
                id: "xdown",
                stacked: true,
                ticks: {
                  callback: function (value, index, values) {
                    return moment(value, "YYYY-MM-DD").format("MMM Do");
                  },
                },
                categoryPercentage: 0.85,
                time: {
                  unit: "day",
                  displayFormats: { day: "MMM Do" },
                  tooltipFormat: "dddd, MMMM Do",
                },
                gridLines: { display: false },
              },
            ],
            yAxes: [
              {
                display: false,
                id: "yleft",
                position: "left",
                type: "linear",
                scaleLabel: {
                  display: true,
                  labelString: "Hits",
                },
                gridLines: { display: false },
                stacked: false,
                ticks: {
                  beginAtZero: false,
                  maxTicksLimit: 12,
                  callback: function (value, index, values) {
                    return Math.round(value);
                  },
                },
              },
            ],
          },
        },
      });
    }
  }

  Chart.defaults.doughnutLabels = Chart.helpers.clone(Chart.defaults.doughnut);
  var loginlockdown_doughnut_helpers = Chart.helpers;
  Chart.controllers.doughnutLabels = Chart.controllers.doughnut.extend({
    updateElement: function (arc, index, reset) {
      var _this = this;
      var chart = _this.chart,
        chartArea = chart.chartArea,
        opts = chart.options,
        animationOpts = opts.animation,
        arcOpts = opts.elements.arc,
        centerX = (chartArea.left + chartArea.right) / 2,
        centerY = (chartArea.top + chartArea.bottom) / 2,
        startAngle = opts.rotation, // non reset case handled later
        endAngle = opts.rotation, // non reset case handled later
        dataset = _this.getDataset(),
        circumference = reset && animationOpts.animateRotate ? 0 : arc.hidden ? 0 : _this.calculateCircumference(dataset.data[index]) * (opts.circumference / (2.0 * Math.PI)),
        innerRadius = reset && animationOpts.animateScale ? 0 : _this.innerRadius,
        outerRadius = reset && animationOpts.animateScale ? 0 : _this.outerRadius,
        custom = arc.custom || {},
        valueAtIndexOrDefault = loginlockdown_doughnut_helpers.getValueAtIndexOrDefault;

      loginlockdown_doughnut_helpers.extend(arc, {
        // Utility
        _datasetIndex: _this.index,
        _index: index,

        // Desired view properties
        _model: {
          x: centerX + chart.offsetX,
          y: centerY + chart.offsetY,
          startAngle: startAngle,
          endAngle: endAngle,
          circumference: circumference,
          outerRadius: outerRadius,
          innerRadius: innerRadius,
          label: valueAtIndexOrDefault(dataset.label, index, chart.data.labels[index]),
        },

        draw: function () {
          var ctx = this._chart.ctx,
            vm = this._view,
            sA = vm.startAngle,
            eA = vm.endAngle,
            opts = this._chart.config.options;

          var labelPos = this.tooltipPosition();
          var segmentLabel = (vm.circumference / opts.circumference) * 100;

          ctx.beginPath();

          ctx.arc(vm.x, vm.y, vm.outerRadius, sA, eA);
          ctx.arc(vm.x, vm.y, vm.innerRadius, eA, sA, true);

          ctx.closePath();
          ctx.strokeStyle = vm.borderColor;
          ctx.lineWidth = vm.borderWidth;

          ctx.fillStyle = vm.backgroundColor;

          ctx.fill();
          ctx.lineJoin = "bevel";

          if (vm.circumference > 0.15) {
            // Trying to hide label when it doesn't fit in segment
            ctx.beginPath();
            ctx.font = loginlockdown_doughnut_helpers.fontString(opts.defaultFontSize, opts.defaultFontStyle, opts.defaultFontFamily);
            ctx.fillStyle = "#fff";
            ctx.textBaseline = "top";
            ctx.textAlign = "center";

            // Round percentage in a way that it always adds up to 100%
            ctx.fillText(segmentLabel.toFixed(0) + "%", labelPos.x, labelPos.y);
          }
        },
      });

      var model = arc._model;
      model.backgroundColor = custom.backgroundColor ? custom.backgroundColor : valueAtIndexOrDefault(dataset.backgroundColor, index, arcOpts.backgroundColor);
      model.hoverBackgroundColor = custom.hoverBackgroundColor ? custom.hoverBackgroundColor : valueAtIndexOrDefault(dataset.hoverBackgroundColor, index, arcOpts.hoverBackgroundColor);
      model.borderWidth = custom.borderWidth ? custom.borderWidth : valueAtIndexOrDefault(dataset.borderWidth, index, arcOpts.borderWidth);
      model.borderColor = custom.borderColor ? custom.borderColor : valueAtIndexOrDefault(dataset.borderColor, index, arcOpts.borderColor);

      // Set correct angles if not resetting
      if (!reset || !animationOpts.animateRotate) {
        if (index === 0) {
          model.startAngle = opts.rotation;
        } else {
          model.startAngle = _this.getMeta().data[index - 1]._model.endAngle;
        }

        model.endAngle = model.startAngle + model.circumference;
      }

      arc.pivot();
    },
  });

  function create_fails_device_chart() {
    if (!loginlockdown_vars.stats_fails_devices || !loginlockdown_vars.stats_fails_devices.percent.length) {
      $("#loginlockdown_fails_devices_chart").remove();
      return;
    } else {
      if (!loginlockdown_vars.is_active) {
        return;
      }
      if (loginlockdown_fails_device_chart) loginlockdown_fails_device_chart.destroy();
      devices_fails_chart = new Chart(document.getElementById("loginlockdown_fails_devices_chart").getContext("2d"), {
        type: "doughnutLabels",
        data: {
          datasets: [
            {
              data: loginlockdown_vars.stats_fails_devices.percent,
              backgroundColor: [loginlockdown_vars.chart_colors[0], loginlockdown_vars.chart_colors[1], loginlockdown_vars.chart_colors[2], loginlockdown_vars.chart_colors[3]],
            },
          ],
          labels: loginlockdown_vars.stats_fails_devices.labels,
        },
        options: {
          animation: false,
          responsive: true,
          segmentShowStroke: false,
          legend: {
            display: false,
          },
          tooltips: {
            callbacks: {
              label: function (tooltipItem, data) {
                var dataset = data.datasets[tooltipItem.datasetIndex];
                return data.labels[tooltipItem.index] + ": " + dataset.data[tooltipItem.index];
              },
            },
          },
        },
      });
    }
  }

  function create_locks_device_chart() {
    if (!loginlockdown_vars.stats_locks_devices || !loginlockdown_vars.stats_locks_devices.percent.length) {
      $("#loginlockdown_locks_devices_chart").remove();
    } else {
      if (!loginlockdown_vars.is_active) {
        return;
      }
      if (loginlockdown_locks_device_chart) loginlockdown_locks_device_chart.destroy();
      devices_locks_chart = new Chart(document.getElementById("loginlockdown_locks_devices_chart").getContext("2d"), {
        type: "doughnutLabels",
        data: {
          datasets: [
            {
              data: loginlockdown_vars.stats_locks_devices.percent,
              backgroundColor: [loginlockdown_vars.chart_colors[0], loginlockdown_vars.chart_colors[1], loginlockdown_vars.chart_colors[2], loginlockdown_vars.chart_colors[3]],
            },
          ],
          labels: loginlockdown_vars.stats_locks_devices.labels,
        },
        options: {
          animation: false,
          responsive: true,
          legend: {
            display: false,
          },
          tooltips: {
            callbacks: {
              label: function (tooltipItem, data) {
                var dataset = data.datasets[tooltipItem.datasetIndex];
                return data.labels[tooltipItem.index] + ": " + dataset.data[tooltipItem.index];
              },
            },
          },
        },
      });
    }
  }

  if ($(".loginlockdown-chart-locks").length && window.localStorage.getItem("loginlockdown_locks_chart") == "enabled") {
    $(".loginlockdown-chart-locks").show();
    create_locks_chart();
    create_locks_device_chart();
  }

  if ($(".loginlockdown-chart-fails").length && window.localStorage.getItem("loginlockdown_fails_chart") == "enabled") {
    $(".loginlockdown-chart-fails").show();
    create_fails_chart();
  }

  if (window.localStorage.getItem("loginlockdown_fails_stats") == "enabled") {
    $(".loginlockdown-stats-fails").show();
    create_fails_device_chart();
  }

  if ($(".loginlockdown-chart-locks").length && window.localStorage.getItem("loginlockdown_locks_chart") == "enabled") {
    $(".loginlockdown-chart-locks").show();
    create_locks_chart();
  }

  if (window.localStorage.getItem("loginlockdown_locks_stats") == "enabled") {
    $(".loginlockdown-stats-locks").show();
    create_locks_device_chart();
  }

  $("#loginlockdown_tabs").on("tabsactivate", function (event, ui) {
    var active_index = $("#loginlockdown_tabs").tabs("option", "active");
    var active_id = $("#loginlockdown_tabs > ul > li").eq(active_index).find("a").attr("href").replace("#", "");
    if (active_id == "loginlockdown_locks_log") {
      if (window.localStorage.getItem("loginlockdown_locks_chart") == "enabled") {
        create_locks_chart();
        create_locks_device_chart();
      }
    } else if (active_id == "loginlockdown_fails_log") {
      if (window.localStorage.getItem("loginlockdown_fails_chart") == "enabled") {
        create_fails_chart();
        create_fails_device_chart();
      }
    } else if (active_id == "loginlockdown_geoip") {
      load_geoip_map();
    }
  });

  if (window.localStorage.getItem("loginlockdown_locks_chart") == null) {
    window.localStorage.setItem("loginlockdown_locks_chart", "enabled");
  }

  if (window.localStorage.getItem("loginlockdown_fails_chart") == null) {
    window.localStorage.setItem("loginlockdown_fails_chart", "enabled");
  }

  if (window.localStorage.getItem("loginlockdown_locks_stats") == null) {
    window.localStorage.setItem("loginlockdown_locks_stats", "enabled");
  }

  if (window.localStorage.getItem("loginlockdown_fails_stats") == null) {
    window.localStorage.setItem("loginlockdown_fails_stats", "enabled");
  }

  $("#anonymous_logging").on("click", function (e) {
    e.preventDefault();

    var title = "";
    var text = "";
    var button = "";
    var anonymous_logging = $("#anonymous_logging").is(":checked");

    if (anonymous_logging) {
      anonymous_logging = false;
      title = "Enable Anonymous Logging?";
      text = "Enabling Anonymous Logging will cause both Lockdown Logs and Fails Logs to be reset";
      button = "Enable";
    } else {
      anonymous_logging = true;
      title = "Disable Anonymous Logging?";
      text = "Disabling Anonymous Logging will cause both Lockdown Logs and Fails Logs to be reset";
      button = "Disable";
    }

    loginlockdown_swal
      .fire({
        title: title,
        type: "question",
        text: text,
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: button,
        cancelButtonText: loginlockdown_vars.cancel_button,
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          $("#anonymous_logging").prop("checked", !anonymous_logging);

          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "toggle_anonymous",
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
            })
            .done(function (response) {
              location.reload();
            });
        } else {
          $("#anonymous_logging").prop("checked", anonymous_logging);
        }
      });
  });

  if ($("#loginlockdown_tabs").tabs("option", "active") == 3) {
    load_geoip_map();
  }

  $("#lockdown_run_tests").on("click", function (e) {
    e.preventDefault();
    $(this).blur();

    loginlockdown_swal.fire({
      title: "Running tests",
      text: " ",
      type: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      imageUrl: loginlockdown_vars.icon_url,
      onOpen: () => {
        $(loginlockdown_swal.getImage()).addClass("loginlockdown_rotating");
      },
      imageWidth: 58,
      imageHeight: 58,
      imageAlt: "Running Tests",
    });

    $.ajax({
      url: ajaxurl,
      data: {
        action: "loginlockdown_run_tool",
        _ajax_nonce: loginlockdown_vars.run_tool_nonce,
        tool: "login_tests",
      },
    })
      .done(function (data) {
        if (data.success) {
          loginlockdown_swal.fire({
            title: "Test Completed",
            text: data.data.message,
            type: data.data.pass ? "success" : "error",
            showConfirmButton: true,
          });
        } else {
          loginlockdown_swal.fire({
            type: "error",
            title: loginlockdown_vars.undocumented_error,
          });
        }
      })
      .fail(function (data) {
        loginlockdown_swal.fire({
          type: "error",
          title: loginlockdown_vars.undocumented_error,
        });
      });
  });

  $("#lockdown_send_email").on("click", function (e) {
    e.preventDefault();
    $(this).blur();

    loginlockdown_swal.fire({
      title: "Sending test email",
      text: " ",
      type: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      imageUrl: loginlockdown_vars.icon_url,
      onOpen: () => {
        $(loginlockdown_swal.getImage()).addClass("loginlockdown_rotating");
      },
      imageWidth: 58,
      imageHeight: 58,
      imageAlt: "Sending test email",
    });

    $.ajax({
      url: ajaxurl,
      data: {
        action: "loginlockdown_run_tool",
        _ajax_nonce: loginlockdown_vars.run_tool_nonce,
        tool: "email_test",
      },
    })
      .done(function (data) {
        if (data.success) {
          loginlockdown_swal.fire({
            title: data.data.title,
            html: data.data.text,
            type: data.data.sent ? "success" : "error",
            showConfirmButton: true,
          });
        } else {
          loginlockdown_swal.fire({
            type: "error",
            title: loginlockdown_vars.undocumented_error,
          });
        }
      })
      .fail(function (data) {
        loginlockdown_swal.fire({
          type: "error",
          title: loginlockdown_vars.undocumented_error,
        });
      });
  });

  $(".create-temporary-link").on("click", function (e) {
    e.preventDefault();
    $(this).blur();

    loginlockdown_swal.fire({
      title: "Creating temporary login link",
      text: " ",
      type: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      imageUrl: loginlockdown_vars.icon_url,
      onOpen: () => {
        $(loginlockdown_swal.getImage()).addClass("loginlockdown_rotating");
      },
      imageWidth: 58,
      imageHeight: 58,
      imageAlt: "Creating temporary login link",
    });

    $.ajax({
      url: ajaxurl,
      data: {
        action: "loginlockdown_run_tool",
        _ajax_nonce: loginlockdown_vars.run_tool_nonce,
        tool: "create_temporary_link",
        user: $('#temp_link_user').val(),
        lifetime: $('#temp_link_lifetime').val(),
        uses: $('#temp_link_uses').val()
      },
    })
      .done(function (data) {
        if (data.success) {
          loginlockdown_swal.fire({
            title: "Temporary link created",
            html: data.data.link,
            type: "success",
            showConfirmButton: true,
            width: 700,
          });

          $('#loginlockdown_temp_links tbody').html(data.data.html);
        } else {
          loginlockdown_swal.fire({
            type: "error",
            title: loginlockdown_vars.undocumented_error,
          });
        }
      })
      .fail(function (data) {
        loginlockdown_swal.fire({
          type: "error",
          title: loginlockdown_vars.undocumented_error,
        });
      });
  });


  $(".settings_page_loginlockdown").on("click", ".delete_temporary_link", function (e) {
    e.preventDefault();
    uid = $(this).data("lock-uid");
    button = $(this);

    loginlockdown_swal
      .fire({
        title: $(button).data("title"),
        type: "question",
        text: $(button).data("text"),
        heightAuto: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: $(button).data("btn-confirm"),
        cancelButtonText: loginlockdown_vars.cancel_button,
        width: 600,
      })
      .then((result) => {
        if (typeof result.value != "undefined") {
          block = block_ui($(button).data("msg-wait"));
          $.post({
            url: ajaxurl,
            data: {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_vars.run_tool_nonce,
              tool: "delete_temporary_link",
              link_id: $(button).data("link-id"),
            },
          })
            .always(function (response) {
              loginlockdown_swal.close();
            })
            .done(function (response) {
              if (response.success) {
                $('#loginlockdown_temp_links tbody').html(response.data.html);
              } else {
                loginlockdown_swal.fire({
                  type: "error",
                  heightAuto: false,
                  title: loginlockdown_vars.documented_error + " " + data.data,
                });
              }
            })
            .fail(function (response) {
              loginlockdown_swal.fire({
                type: "error",
                heightAuto: false,
                title: loginlockdown_vars.undocumented_error,
              });
            });
        } // if confirmed
      });
  });

  $(".settings_page_loginlockdown").on("click", ".loginlockdown-temporary-link", function (e) {
    e.preventDefault();
    let temporary_link_href = $(this).attr('href');
    navigator.clipboard.writeText(temporary_link_href);

    $(this).html('Copied');
    $(this).addClass('loginlockdown-temporary-link-copied');

    setTimeout(
        loginlockdown_temporary_link_after_copy.bind(null, $(this), temporary_link_href),
    500);
  });

  function loginlockdown_temporary_link_after_copy(temporary_link, temporary_link_href){
    temporary_link.html(temporary_link_href);
    temporary_link.removeClass('loginlockdown-temporary-link-copied');
  }

  $("#lockdown_recovery_url_show").on("click", function (e) {
    e.preventDefault();
    $(this).blur();

    loginlockdown_swal.fire({
      title: "Recovery URL",
      html: "<strong id='lockdown_recovery_url'></strong><br /><br /><button class='button button-primary' id='lockdown_recovery_url_reset'>Reset Recovery URL</button>",
      type: false,
      allowOutsideClick: true,
      allowEscapeKey: true,
      allowEnterKey: true,
      showConfirmButton: true,
    });


    get_recovery_url(false);
  });

  $(".settings_page_loginlockdown").on("click", "#lockdown_recovery_url_reset", function (e){
    $(this).blur();
    $("#lockdown_recovery_url").html('<img src="' + loginlockdown_vars.icon_url + '" />');
    get_recovery_url(true);
  });

  function get_recovery_url(reset){
    $.post({
        url: ajaxurl,
        data: {
          action: "loginlockdown_run_tool",
          _ajax_nonce: loginlockdown_vars.run_tool_nonce,
          tool: "recovery_url",
          reset: reset,
        },
      })
        .done(function (data) {
          $("#lockdown_recovery_url").html(data.data.url);
        })
        .fail(function (data) {
          loginlockdown_swal.fire({
            type: "error",
            title: loginlockdown_vars.undocumented_error,
          });
      });
  }

  $(document).on("click", ".loginlockdown-upload", function (e) {
      
    e.preventDefault();
    if ($(this).hasClass("loginlockdown-free-images")) {
      getUploader("Select Image", $(this), true);
    } else {
      getUploader("Select Image", $(this), false);
    }
  });

  // Removing photo from the canvas and emptying the text field
  $(document).on("click", ".loginlockdown-remove-image", function (e) {
    e.preventDefault();
    $(this).parent().parent().find("input").val("");
    $(this).parent().parent().find(".loginlockdown-preview-area").html("Select an image or upload a new one");
    $(this).hide();
  });

  var unsplash_page = 1;
  var total_pages = 9999;
  var total_results = 0;
  var unsplash_search_query = "";
  var custom_uploader;

  function loginlockdown_get_unsplash_images() {
    jQuery
      .ajax({
        url: ajaxurl,
        method: "POST",
        crossDomain: true,
        dataType: "json",
        timeout: 30000,
        data: {
          action: "loginlockdown_unsplash_api",
          page: unsplash_page,
          per_page: 60,
          search: unsplash_search_query,
        },
      })
      .success(function (response) {
        var unsplash_images = "";
        var unsplash_html = "";
        if (response.success) {
          if (response.data.results) {
            unsplash_images = JSON.parse(response.data.results);
            total_results = response.data.total_results;
            total_pages = response.data.total_pages;

            for (i in unsplash_images) {
              unsplash_html += '<div class="loginlockdown-unsplash-image" data-id="' + unsplash_images[i]["id"] + '" data-url="' + unsplash_images[i]["full"] + '" data-name="' + unsplash_images[i]["name"] + '">';
              unsplash_html += '<img src="' + unsplash_images[i]["thumb"] + '">';
              unsplash_html += unsplash_images[i]["user"];
              unsplash_html += "</div>";
            }
          }

          unsplash_html += '<div class="loginlockdown_unsplash_pagination">';

          if (total_pages > 1) {
            unsplash_html += total_results.toFixed().replace(/(\d)(?=(\d{3})+(,|$))/g, "$1,") + " images";
          }

          if (unsplash_page > 1) {
            unsplash_html += '<div id="loginlockdown_unsplash_prev">&lt;- Previous</div>';
          }
          if (!total_pages || unsplash_page < total_pages) {
            unsplash_html += '<div id="loginlockdown_unsplash_next">Next -&gt;</div>';
          }
          unsplash_html += "</div>";
          unsplash_html += '<p style="text-align: center;"><small>Powered by <a href="https://unsplash.com/?utm_source=Coming+Soon+demo&utm_medium=referral" target="_blank">Unsplash</a></small></p>';
          jQuery(".unsplash-browser").html(unsplash_html);
        } else {
          jQuery(".unsplash-browser").html('<div class="loginlockdown-loader">An error occured contacting the Unsplash API.<br /><span class="loginlockdown-unsplash-retry">Click here to try again.</span></div>');
        }
      })
      .error(function (type) {
        jQuery(".unsplash-browser").html('<div class="loginlockdown-loader">An error occured contacting the Unsplash API.<br /><span class="loginlockdown-unsplash-retry">Click here to try again.</span></div>');
      });
  }

  $("body").on("click", ".loginlockdown-unsplash-retry", function () {
    $(".unsplash-browser").html(
      '<div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ... </div> '
    );
    loginlockdown_get_unsplash_images();
  });

  $("body").on("click", "#loginlockdown_unsplash_prev", function () {
    $(".unsplash-browser").html(
      '<div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ... </div> '
    );
    unsplash_page--;
    loginlockdown_get_unsplash_images();
  });

  $("body").on("click", "#loginlockdown_unsplash_next", function () {
    $(".unsplash-browser").html(
      '<div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ... </div> '
    );
    unsplash_page++;
    loginlockdown_get_unsplash_images();
  });

  $("body").on("keyup change", "#unsplash_search", function (e) {
    if ($(this).val().length == 0 || $(this).val().length >= 3) {
      $("#unsplash_search_btn").removeAttr("disabled");
      if (e.which == 13) {
        unsplash_execute_search();
      }
    } else {
      $("#unsplash_search_btn").attr("disabled", "disabled");
    }
  });

  $("body").on("click", "#unsplash_search_btn", function () {
    unsplash_execute_search();
  });

  $("body").on("click", ".loginlockdown-unsplash-image", function () {
    $(".loginlockdown-unsplash-image").removeClass("loginlockdown-unsplash-image-selected");
    $(this).addClass("loginlockdown-unsplash-image-selected");
    $(".loginlockdown-media-button-select").removeAttr("disabled");
  });

  function unsplash_execute_search() {
    if (
      $("#unsplash_search").val().length == 0 ||
      $("#unsplash_search").val().length >= 3
    ) {
      $(".unsplash-browser").html(
        '<div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Searching images ... </div> '
      );
      unsplash_search_query = $("#unsplash_search").val();
      unsplash_page = 1;
      loginlockdown_get_unsplash_images();
    } else {
      $("#unsplash_search_btn").attr("disabed", "disabled");
    }
  }

  $(document).on("click", ".loginlockdown-image-upload-button", function (e) {
    e.preventDefault();
    if ($(this).hasClass("loginlockdown-free-images")) {
      getUploader("Select Image", $(this), true);
    } else {
      getUploader("Select Image", $(this), false);
    }
  });

  $(document).on("click", ".loginlockdown-image-upload-remove", function (e) {
    e.preventDefault();
    $(this).parents(".loginlockdown-image-upload-wrapper").children(".loginlockdown-image-upload-input").val("");
    $(this).parent().css("background-image", "");
    $(this)
      .parent()
      .prepend('<img src="' + loginlockdown_vars.url + '/images/image.png" />');
    $(this).parent().children(".loginlockdown-image-upload-remove").remove();
  });

  $("body").on("click", ".media-frame-router .media-router .media-menu-item", function () {
    if ($(this).hasClass("loginlockdown-unsplash-images")) {
      $(".media-menu-item").removeClass("active");
      $(this).addClass("active");
      custom_uploader.content._mode = "unsplash";
      $(".media-button-select").hide();
      $(".loginlockdown-media-button-select").show();
      $(".media-modal-content .media-frame-content").html('<div class="unsplash_head"><button disabled="disabled" id="unsplash_search_btn" class="button button-primary">Search</button><input type="text" id="unsplash_search" placeholder="Search unsplash images..." /></div><div class="unsplash-browser"><div class="loginlockdown-unsplash-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ... </div> </div>');

      loginlockdown_get_unsplash_images();
    } else if ($(this).hasClass("loginlockdown-depositphotos-images")) {
      $(".media-menu-item").removeClass("active");
      $(this).addClass("active");
      custom_uploader.content._mode = "unsplash";
      $(".media-button-select").hide();
      $(".loginlockdown-media-button-select").hide();
      $(".media-modal-content .media-frame-content").html('<div class="depositphotos_head"><button disabled="disabled" id="depositphotos_search_btn" class="button button-primary">Search</button><input type="text" id="depositphotos_search" placeholder="Search depositphotos images..." /></div><div class="depositphotos-browser"><div class="loginlockdown-depositphotos-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ... </div> </div>');

      loginlockdown_get_depositphotos_images();
    } else {
      $(".media-button-select").show();
      $(".loginlockdown-media-button-select").hide();
    }
  });

    // css and html editor
    function getEditor($editorID, $textareaID, $mode) {
        if ($("#" + $editorID).length > 0) {
          var editor = ace.edit($editorID),
            $textarea = $("#" + $textareaID).hide();
    
          editor.getSession().setValue($textarea.val());
    
          editor.getSession().on("change", function () {
            $textarea.val(editor.getSession().getValue());
          });
    
          editor.getSession().setMode("ace/mode/" + $mode);
          //editor.setTheme( 'ace/theme/xcode' );
          editor.getSession().setUseWrapMode(true);
          editor.getSession().setWrapLimitRange(null, null);
          editor.renderer.setShowPrintMargin(null);
    
          editor.session.setUseSoftTabs(null);
        }
      }
    
      getEditor("custom_css_editor", "custom_css", "css");

  $("body").on("click", ".loginlockdown-media-button-select", function () {
    $(".loginlockdown-media-button-select").attr("disabled", "disabled");
    if ($(".media-menu-item.active").hasClass("loginlockdown-unsplash-images")) {
      var loginlockdown_unsplash_id = "";
      var image_input_id = $(this).data("id");
      $(".loginlockdown-unsplash-image-selected").each(function () {
        loginlockdown_unsplash_id = $(this).data("id");
        loginlockdown_unsplash_url = $(this).data("url");
        loginlockdown_unsplash_name = $(this).data("name");
      });

      if (loginlockdown_unsplash_id != "") {
        $(".media-modal-content .media-frame-content").html('<div class="unsplash-browser"><div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Downloading image ... </div> </div>');
        $.ajax({
          url: ajaxurl,
          method: "POST",
          crossDomain: true,
          dataType: "json",
          timeout: 300000,
          data: {
            action: "loginlockdown_unsplash_download",
            image_id: loginlockdown_unsplash_id,
            image_url: loginlockdown_unsplash_url,
            image_name: loginlockdown_unsplash_name,
          },
        })
          .success(function (response) {
            if (response.success) {
              if (response.data) {
                $("#" + image_input_id)
                  .parent()
                  .css("background-image", response.data);
                $("#" + image_input_id)
                  .parent()
                  .find(".loginlockdown-upload-append")
                  .html('&nbsp;<a href="javascript: void(0);" class="loginlockdown-remove-image">Remove</a>');
                $("#" + image_input_id).val(response.data);
                $("#" + image_input_id).parents('.loginlockdown-image-upload-wrapper').children('.loginlockdown-image-upload-preview-wrapper').css('background-image', "url(" + response.data + ")");
                custom_uploader.close();
              }
            } else {
              $(".unsplash-browser").html(response.data);
              var message = "An error occured downloading the image.";
              if (response.data) {
                message = response.data;
              }
              $(".unsplash-browser").html('<div class="loginlockdown-loader">' + message + '<br /><span class="loginlockdown-unsplash-retry">Click here to return to browsing.</span></div>');
            }
          })
          .error(function (type) {
            $(".unsplash-browser").html('<div class="loginlockdown-loader">An error occured downloading the image.<br /><span class="loginlockdown-unsplash-retry">Click here to return to browsing.</span></div>');
          })
          .always(function (type) {
            $(".loginlockdown-media-button-select").removeAttr("disabled");
          });
      }
    }
  });

  function getUploader($text, $target, $unsplash) {
    if (custom_uploader) {
      custom_uploader.detach();
    }

    // Extend the wp.media object
    custom_uploader = wp.media.frames.file_frame = wp.media({
      title: $text,
      button: {
        text: $text,
      },
      multiple: false,
    });

    if ($unsplash) {
      custom_uploader.on("open", function () {
        var image_input_id = $target.parents('.loginlockdown-image-upload-wrapper').children(".loginlockdown-image-upload-input").attr("id");
        
        if (!jQuery(".media-frame-router .media-router .loginlockdown-unsplash-images").length) {
          jQuery(".media-frame-router .media-router").append('<a href="#" class="media-menu-item loginlockdown-unsplash-images">Unsplash (free images)</a>');
        }

        unsplash_search_query = "";

        $(".media-menu-item").removeClass("active");
        $(".loginlockdown-unsplash-images").addClass("active");
        custom_uploader.content._mode = "unsplash";

        $(".media-button-select").hide();
        $(".loginlockdown-media-button-select").show();
        $(".media-modal-content .media-frame-content").html('<div class="unsplash_head"><button disabled="disabled" id="unsplash_search_btn" class="button button-primary">Search</button><input type="text" id="unsplash_search" placeholder="Search images..." /></div><div class="unsplash-browser"><div class="loginlockdown-loader"><span class="dashicons dashicons-spin dashicons-update"></span>&nbsp; Loading images ...</div> </div>');

        if (jQuery(".media-toolbar .loginlockdown-media-button-select").length) {
          jQuery(".media-toolbar .loginlockdown-media-button-select").remove();
        }
        jQuery(".media-button-select").after('<button type="button" disabled="disabled" ' + (jQuery(".media-menu-item.active").hasClass("loginlockdown-unsplash-images") ? "" : ' style="display:none" ') + ' class="button button-primary button-large media-button loginlockdown-media-button-select" data-id="' + image_input_id + '">Use Selected Image</button>');

        loginlockdown_get_unsplash_images(1);
      });
    }

    // When a file is selected, grab the URL and set it as the text field's value
    custom_uploader.on("select", function () {
      var attachment = custom_uploader.state().get("selection").first().toJSON();
      $target.parents(".loginlockdown-image-upload-preview-wrapper").parent().find("input").val(attachment.url);
      $target.parents(".loginlockdown-image-upload-preview-wrapper").find("img").remove();
      $target.parent().css("background-image", "url(" + attachment.url + ")");
      $target.parent().append('<button type="button" class="button loginlockdown-image-upload-remove" style="margin-top: 4px">Remove</button>');
    });

    // Open the uploader dialog
    custom_uploader.open();
  }


  $(".settings_page_loginlockdown").on("click", ".open-setup-wizard", function (e) {
    e.preventDefault();
    $(".loginlockdown-wizard-wrapper").show();
  });

  $(".settings_page_loginlockdown").on("click", ".loginlockdown-wizard-button", function (e) {
    $(".loginlockdown-wizard-wrapper").remove();
    e.preventDefault();
    $(this).blur();

    loginlockdown_swal.fire({
      title: "Setting things up",
      text: " ",
      type: false,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      imageUrl: loginlockdown_vars.icon_url,
      onOpen: () => {
        $(loginlockdown_swal.getImage()).addClass("loginlockdown_rotating");
      },
      imageWidth: 58,
      imageHeight: 58,
      imageAlt: "Setting things up",
    });

    $.post({
      url: ajaxurl,
      data: {
        action: "loginlockdown_run_tool",
        _ajax_nonce: loginlockdown_vars.run_tool_nonce,
        tool: "wizard_setup",
        config: $(this).data("config"),
      },
    })
      .done(function (data) {
        location.reload();
      })
      .fail(function (data) {
        loginlockdown_swal.fire({
          type: "error",
          title: loginlockdown_vars.undocumented_error,
        });
      });
  });

  function load_geoip_map() {
    if (typeof ll_geoip_map != "undefined") {
      return;
    }

    var ll_geoip_fill = "#29b99a";

    if(loginlockdown_vars.chart_colors != null){
        ll_geoip_fill = loginlockdown_vars.chart_colors[0];
    }

    ll_geoip_map = new Datamap({
      element: document.getElementById("geoip_map"),
      fills: {
        defaultFill: ll_geoip_fill,
        failed: "#ff0000",
      },
      geographyConfig: {
        highlightOnHover: true,
        highlightFillColor: "#61e1c5",
      },
    });
    
    ll_geoip_map.bubbles(loginlockdown_vars.stats_map, {
      popupTemplate: function (geo, data) {
        return '<div class="hoverinfo">' + data.name + ": " + data.radius + "% of fails";
      },
    });

    $(window).on("resize", function () {
      ll_geoip_map.resize();
    });
  }
});


