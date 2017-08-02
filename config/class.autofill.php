<?php



/**
 * @class AutoFill
 *
 * automatic fill options for text fields
 *
 * Carlos Reche
 * carlosreche@yahoo.com
 * Feb 14, 2005 - Aug 21, 2005
 */
class AutoFill {
    var $field_id;
    var $values;
    var $limit;
    var $submit_on_fill;
    var $javascript_loaded;

    function AutoFill($field_id, $values = array(), $limit = 10, $submit_on_fill = false) {
        $this->field_id          = (string)$field_id;
        $this->values            = (array)$values;
        $this->limit             = (int)$limit;
        $this->submit_on_fill    = (bool)$submit_on_fill;
        $this->javascript_loaded = false;
    }


    function create($bool_return = false) {
        $var_name = "AutoFill_" . $this->field_id;

        $html  = $this->loadJavascript(true);
        $html .= '
    <script type="text/javascript">
        ' . $var_name . ' = new AutoFill("' . $this->field_id . '");
        ' . $var_name . '.setLimit(' . (int)$this->limit . ');
        ' . $var_name . '.submitOnFill(' . ($this->submit_on_fill ? 'true' : 'false') . ');';
        foreach ($this->options as $option)
            $html .= "\n".'        ' . $var_name . '.addOption("' . $option . '");';
        $html .= '
    </script>';
        return $this->printHTML($html, $bool_return);
    }


    function addOption($option) {
        $this->options[] = (string)$option;
    }

    function setLimit($limit) {
        $this->limit = (int)$limit;
    }

    function submitOnFill($bool_submit = true) {
        $this->submit_on_fill = (bool)$bool_submit;
    }



    function loadJavascript($bool_return = false) {
        if ($this->javascript_loaded) {
            return;
        }
        $this->javascript_loaded = true;

        $html = '
    <script type="text/javascript">
        /**
         * automatic fill for text fields
         *
         * Carlos Reche
         * carlosreche@yahoo.com
         * Feb 14, 2005 - Aug 21, 2005
         */
        function AutoFill(fieldId) {
          if (!document.customProperties) {
            document.customProperties = [];
          }
          if (!document.customProperties[fieldId]) {
            document.customProperties[fieldId] = {};
          }
          document.customProperties[fieldId].autoFill = {
            field: null,
            list: null,
            options: [],
            limit: 10,
            submitOnFill: false,
            currentSelection: -1
          };
          var autoFill = document.customProperties[fieldId].autoFill;
          this.id = fieldId;
          this.addOption = function(value) {
            autoFill.options.push(value);
            autoFill.options.sort();
          };
          this.setLimit = function(limit) {
            autoFill.limit = limit;
          };
          this.submitOnFill = function(boolSubmit) {
            autoFill.submitOnFill = typeof boolSubmit == "undefined" ? true : boolSubmit;
          };

          if (window.addEventListener) {
            window.addEventListener("load", function() {AutoFill.init(fieldId)}, true);
          } else if (window.attachEvent) {
            window.attachEvent("onload", function() {AutoFill.init(fieldId)});
          }
        }


        AutoFill.init = function(fieldId) {
          var field = document.getElementById(fieldId), autoFill;
          if (!field) {
            throw new Error("element not found");
          }
          field.autoFill = autoFill = document.customProperties[fieldId].autoFill;
          var cont = document.createElement("div"), box = document.createElement("div");
          var list = document.createElement("ul");

          cont.style.display = "inline";
          cont.style.position = "relative";
          field = field.parentNode.replaceChild(cont, field);
          cont.appendChild(field);
          cont.appendChild(box);
          box.appendChild(list);


          field.onkeydown = function(e) {
            if (!e) {
              e = window.event;
            }
            var keyCode = (e.keyCode ? e.keyCode : (e.which ? e.which : 0));
            var autoFill = document.customProperties[this.id].autoFill;
            var list = autoFill.list, current = autoFill.currentSelection;

            if ((keyCode == 38) || (keyCode == 40)) {
              var i = current + (keyCode == 38 ? -1 : 1);
              AutoFill.moveSelectionTo(list, i);
            } else if (keyCode == 13) {
              if ((current > -1) && (current < list.childNodes.length)) {
                var item = list.childNodes.item(current);
                field.nextSibling.hide();
                autoFill.currentSelection = -1;
                if (item.className != "") {
                  field.value = item.innerHTML;
                  list.clear();
                  return false;
                }
              }
            }
          };
          field.onkeyup = function(e) {
            if (!e) {
              e = window.event;
            }
            var keyCode = (e.keyCode ? e.keyCode : (e.which ? e.which : 0));
            var autoFill = document.customProperties[this.id].autoFill;
            var list = autoFill.list, box = list.parentNode;

            if ((keyCode != 38) && (keyCode != 40) && (keyCode != 13)) {
              list.clear();
              if (this.value == "") {
                box.style.display = "none";
              } else {
                var p = this.value.replace(/([\\\|\.\+\*\?\[\^\(\$\)])/gi, "\\$1");
                var pattern = new RegExp(("^"+ p +".+$"), "i"), i = -1, j = 0;
                while (++i < autoFill.options.length) {
                  if (autoFill.options[i].match(pattern)) {
                    if ((j < autoFill.limit) || (autoFill.limit < 0)) {
                      list.addItem(autoFill.options[i], j);
                    }
                    j++;
                  }
                }
                if ((j > autoFill.limit) && (autoFill.limit > -1)) {
                  var more = document.createElement("li");
                  more.innerHTML = "...";
                  list.appendChild(more);
                }
                if (list.childNodes.length > 0) {
                  box.show();
                } else {
                  box.hide();
                }
              }
            }
          };
          field.onfocus = function() {
            var autoFill = document.customProperties[this.id].autoFill;
            var list = autoFill.list, box = list.parentNode;
            if (list.childNodes.length > 0) {
              box.show();
            }
          };
          field.onblur = function() {
            var box = this.nextSibling;
            setTimeout(function() {box.hide()}, 300);
          };

          box.className = "autofill-box";
          box.style.display = "none";
          box.style.position = "absolute";
          box.style.top = (field.clientHeight + 4) + "px";
          box.style.left = "0";
          box.show = function() {box.style.display = "";};
          box.hide = function() {box.style.display = "none";};

          list.style.margin = "0";
          list.style.padding = "0";
          list.style.listStyle = "none";
          list.clear = function() {
            for (var i = list.childNodes.length - 1; i >= 0; i--) {
              list.removeChild(list.childNodes.item(i));
            }
          };
          list.addItem = function(value, index) {
            var li = document.createElement("li");
            li.className = (index == field.autoFill.currentSelection) ? "selection" : "selectable";
            li.innerHTML = value;
            li.index = index;
            li.style.cursor = "pointer";
            list.appendChild(li);
            li.onclick = function() {
              field.focus();
              field.value = value;
              if (field.autoFill.submitOnFill) {
                field.form.submit();
              }
            };
            li.onmouseover = function() {
              AutoFill.moveSelectionTo(list, li.index);
            };
            li.onmouseout = function() {
              field.autoFill.currentSelection = -1;
              li.className = "selectable";
            };
          };
          field.autoFill.field = field;
          field.autoFill.list = list;
        }


        AutoFill.moveSelectionTo = function(list, index) {
          var autoFill = list.parentNode.previousSibling.autoFill;
          var current = autoFill.currentSelection;

          if ((current > -1) && (current < list.childNodes.length)) {
            var currentItem = list.childNodes.item(current);
            if (currentItem.className != "") {
              currentItem.className = "selectable";
            }
          }
          if (index < 0) {
            autoFill.currentSelection = -1;
          } else if (index >= list.childNodes.length) {
            autoFill.currentSelection = list.childNodes.length;
            if (list.lastChild && (list.lastChild.className == "")) {
              autoFill.currentSelection--;
            }
          } else {
            autoFill.currentSelection = index;
            var item = list.childNodes.item(index);
            if (item.className != "") {
              item.className = "selection";
            }
          }
        }

    </script>';
        return $this->printHTML($html, $bool_return);
    }



    function printHTML($html, $bool_return)
    {
        if ($bool_return)
            return $html;
        echo $html;
        return true;
    }
}



?>

