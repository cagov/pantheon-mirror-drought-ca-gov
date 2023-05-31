/**
 * LoginLockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

// global object for all LoginLockdown functions

jQuery(document).ready(function ($) {
  if (typeof loginlockdown_rebranding != "undefined") {
    if ($('[data-slug="login-lockdown"]').length > 0) {
      $('[data-slug="login-lockdown"]')
        .children(".plugin-title")
        .children("strong")
        .html("<strong>" + loginlockdown_rebranding.name + "</strong>");
    }
  }

  if (typeof loginlockdown_pointers != "undefined") {
    $.each(loginlockdown_pointers, function (index, pointer) {
      if (index.charAt(0) == "_") {
        return true;
      }
      $(pointer.target)
        .pointer({
          content: "<h3>Login Lockdown Pro</h3><p>" + pointer.content + "</p>",
          pointerWidth: 380,
          position: {
            edge: pointer.edge,
            align: pointer.align,
          },
          close: function () {
            $.get(ajaxurl, {
              action: "loginlockdown_run_tool",
              _ajax_nonce: loginlockdown_pointers.run_tool_nonce,
              tool: "loginlockdown_dismiss_pointer",
            });
          },
        })
        .pointer("open");
    });
  }
}); // on ready
