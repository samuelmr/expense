  function loginFocus() {
    if (document.forms &&
           document.forms['loginform'] &&
           document.forms['loginform'].elements['login_username'] &&
           document.forms['loginform'].elements['login_username'].focus) {
           document.forms['loginform'].elements['login_username'].select();
           document.forms['loginform'].elements['login_username'].focus();
    }
  }
