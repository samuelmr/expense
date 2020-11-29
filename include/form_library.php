<?php

  // these defaults may be overridden in calling script
  // see function make_id to see how these are used
  $FORMS['id_allowed_chars'] = 'a-zA-Z0-9_';
  $FORMS['id_join_string'] = '__';
  $FORMS['id_escape_start'] = "'E'";
  $FORMS['id_escape_end'] = "'E'";
  $GLOBALS['FORMS'] = $FORMS;

  function form_start($action,
                      $id=NULL,
                      $method='get',
                      $enctype=NULL,
                      $accept=NULL) {
    return "<form action=\"$action\"".
           ($id ? " id=\"$id\"" : '').
           " method=\"".($method == 'post' ? 'post' : 'get')."\"".
           ($enctype ? " enctype=\"$enctype\"" : '').
           ($accept ? " accept=\"$accept\"" : '').
           ">";
  }

  function form_end() {
    return "</form>";
  }

  function form_label($name, $value, $text,
                      $misc=NULL, $encode=TRUE, $form=NULL) {
    return "<label for=\"".make_id($name, $value, $form)."\"".
           ($misc ? " $misc" : "").
           ">".
           ($encode ? htmlentities($text) : $text).
           "</label>";
  }

  function form_checkbox($name,
                         $value,
                         $checked=NULL,
                         $attrs=NULL,
                         $encode=FALSE,
                         $form=NULL) {
    $checkbox = "<input type=\"checkbox\"".
                " id=\"".make_id($name, $value, $form)."\" name=\"$name"."[]\"";
    $checkbox .= " value=\"".($encode ? htmlentities($value) : $value)."\"";
    if (gettype($checked) == 'boolean' && ($checked === true)) {
      $checkbox .= " checked=\"checked\"";
    }
    ## what about numerics!!!
    elseif ((gettype($checked) == 'string') &&
            (($value == $checked) || (htmlentities($value) == $checked))) {
      $checkbox .= " checked=\"checked\"";
    }
    elseif ((gettype($checked) == 'array') &&
            (in_array($value, $checked, true) ||
             (!in_array($value, $checked, true) &&
              in_array($value, $checked, false)) ||
             in_array(htmlentities($value), $checked, true) ||
             (!in_array(htmlentities($value), $checked, true) &&
              in_array(htmlentities($value), $checked, false)))) {
      $checkbox .= " checked=\"checked\"";
    }
    if ($attrs) {
      $checkbox .= " $attrs";
    }
    $checkbox .= " />";
    return($checkbox);
  } // form_checkbox
  


  function form_input($name,
                      $value=NULL,
                      $type='text',
                      $size=NULL,
                      $attrs=NULL,
                      $encode=FALSE,
                      $form=NULL) {
    /**
     * string form_input(string $name
     *                   [, string $value
     *                      [, string $type
     *                         [, int $size
     *                            [, int $attributes
     *                               [, bool $htmlencode]]]]]);
     *
     * return HTML INPUT text field $name with value $value
     *
     * $size is the (approximate) length of the text field
     * $type may be 'text', 'password' or 'hidden'
     * $attrs is a string which may contain additional attributes
     * (e.g. onfocus="...", class="...", etc).   
     * 
     * Example usage:
     * $textfieldname = 'name';
     * echo 'What is your whole name?<BR>';
     * echo form_input($textfieldname, $$textfieldname, 40);
     *     
     */

    $input = "<input type=\"$type\"";
    if ($name) {
      $input .= " id=\"".make_id($name, $value, $form)."\" name=\"$name\"";
    }
    if ($size) {
      $input .= " size=\"$size\"";
    }
    $input .= " value=\"".($encode ? htmlentities($value) : $value)."\"";
    if ($attrs) {
      $input .= " $attrs";
    }
    $input .= " />";
    return($input);
  } // form_input
  


  function form_radio($name,
                      $value,
                      $checked=NULL,
                      $attrs=NULL,
                      $encode=FALSE,
                      $form=NULL) {
    /**
     * string form_radio(string $name,
     *                   string $value
     *                   [, mixed $checked
     *                      [, string $attributes
     *                         [, bool $htmlencode]]]);
     *
     * return HTML RADIO BUTTON input field $name with value $value
     *
     * if $checked has a boolean value 'true',
     * radio button is explicitly marked as checked
     *
     * if $checked is a string value, radio button is marked as
     * checked only if it's value equals to $checked
     * 
     * $attrs is a string which may contain additional attributes
     * (e.g. onclick="...", class="...", etc).   
     * 
     * Example usage:
     * $radioname = 'languages';
     * echo 'Which languages you know?<BR>';
     * echo form_radio($radioname, 'PHP', $$radioname);
     * echo 'PHP<BR>';
     * echo form_radio($radioname, 'Java', $$radioname);
     * echo 'Java<BR>';    
     * echo form_radio($radioname, 'Perl', $$radioname);
     * echo 'Perl<BR>';    
     *     
     */
     
    $radio = "<input type=\"radio\"".
             " id=\"".make_id($name, $value, $form)."\" name=\"$name\"".
             " value=\"".($encode ? htmlentities($value) : $value)."\"";
    if (gettype($checked) == 'boolean' && ($checked === true)) {
      $radio .= " checked=\"checked\"";
    }
    elseif (gettype($checked) == 'string') {
      if (($value == $checked) || ($value == htmlentities($checked))) {
        $radio .= " checked=\"checked\"";
      }
    }
    if ($attrs) {
      $radio .= " $attrs";
    }
    $radio .= " />";
    return($radio);
  } // form_radio



  function form_selectlist($name,
                           &$options,
                           $selected=NULL,
                           $size=1,
                           $multiple=NULL,
                           $attrs=NULL,
                           $encode=FALSE,
                           $form=NULL) {

    $optgroup = NULL; // not yet in use!

    reset($options);
     
    $selectlist = "<select id=\"".make_id($name, NULL, $form)."\"".
                  " name=\"$name";
    if ($multiple) {
      $selectlist .= "[]\""; // returns PHP array!
      $selectlist .= " multiple=\"multiple\"";
    }
    else {
      $selectlist .= "\""; 
    }
    if ($size > 1) {
      $selectlist .= " size=\"$size\"";
    }
    if ($attrs) {
      $selectlist .= " $attrs";
    }
    $selectlist .= ">";
    
    $type = gettype($selected);

    // should check, whether $options contain two similar values
    // like 0 and '' and decide, which should be selected
    // -- the third value to in_array() doesn't do the job
    $currgroup = '';
    foreach ($options as $value => $text) {
      // how to pass $optgroup???
      if ($optgroup && ($currgroup != $optgroup)) {
        if ($currgroup) {
          $selectlist .= "</optgroup>";
        }
        $selectlist .= "<optgroup label=\"$optgroup\">";
        $currgroup = $optgroup;
      } 
      $selectlist .= "<option";
      if (($type == 'array') &&
          (($selected[$value] == $text) ||
           in_array($value, $selected, true) ||
           (!in_array($value, $selected, true) &&
            in_array($value, $selected, false)) ||
           in_array(htmlentities($value), $selected))) {
        $selectlist .= " selected=\"selected\"";
      }
      elseif (($type != 'array') && isset($selected) &&
              (($value == $selected) ||
               ($value == htmlentities($selected)))) {
        $selectlist .= " selected=\"selected\"";
      }
      if ($encode) {
        $value = htmlentities($value);
        $text = htmlentities($text);
      }
      $selectlist .= " id=\"".make_id($name, $value, $form).
                     "\" value=\"$value\"";
      $selectlist .= ">$text</option>";
    }
    if ($currgroup) {
       $selectlist .= "</optgroup>";
    }
    $selectlist .= "</select>";
    return($selectlist);
  } // form_selectlist



  function form_textarea($name,
                         $value=NULL,
                         $cols=40,
                         $rows=4,
                         $attrs=NULL,
                         $encode=FALSE,
                         $form=NULL) {
    $textarea = "<textarea id=\"".make_id($name, NULL, $form)."\"".
                " name=\"$name\" cols=\"$cols\" rows=\"$rows\"";
    if ($attrs) {
      $textarea .= " $attrs";
    }
    $textarea .= ">".($encode ? htmlentities($value) : $value)."</textarea>";
    return($textarea);
  } // form_textarea
  

  function make_id($name, $value=NULL, $form=NULL) {
    $FORMS = $GLOBALS['FORMS'];
    # $search = "/([^$FORMS[id_allowed_chars]])/e";
    # $replace = $FORMS['id_escape_start'].".ord('\\1').".
    #            $FORMS['id_escape_end'];
    $search = "/([^$FORMS[id_allowed_chars]])/";
    if ($form) {
     $name = $form.$FORMS['id_join_string'].$name;
    }
    # $id = preg_replace($search, $replace, $name);
    $id = preg_replace_callback(
            $search,
            'form_param_replace',
            $name);
    if (!is_null($value)) {
      $id .= $FORMS['id_join_string'].
        preg_replace_callback(
          $search,
          'form_param_replace',
          $value
        );
    }
    return $id;
  }

  function form_param_replace($matches) {
    global $FORMS; 
    return $FORMS['id_escape_start'].
           ".ord('\\1').".
           $FORMS['id_escape_end'];
  }

?>
