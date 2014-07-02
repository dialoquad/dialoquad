<?php
/*
  Plugin Name: Easy WP LaTeX
  Plugin URI: http://www.thulasidas.com/plugins/easy-latex
  Description: Easiest way to show mathematical equations on your blog using LaTeX. Go to <a href="options-general.php?page=easy-wp-latex-lite.php">Settings &rarr; Easy WP LaTeX</a> to set it up, or use the "Settings" link on the right.
  Version: 4.11
  Author: Manoj Thulasidas
  Author URI: http://www.thulasidas.com
 */

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (class_exists("EzLaTeX")) {
  die("<strong><em>Easy WP LaTeX Pro</em></strong> seems to be active.<br />Please use the Pro version, or deactivate it before activating <strong><em>Easy WP LaTeX Lite</em></strong>.");
}
else {

  require_once('EzOptions.php');

  class EzLaTeX extends EzBasePlugin {

    var $adminMsg;
    // "http://l.wordpress.com/latex.php?latex=";
    // "http://www.bytea.net/cgi-bin/mimetex.cgi?formdata=";
    var $server = "http://l.wordpress.com/latex.php";
    // $img_format should be 'gif' when using mimetex service.
    var $img_format = "png";

    function EzLaTeX() { //constructor
      parent::__construct("easy-latex", "Easy LaTeX", __FILE__);
      $this->prefix = 'ezLaTeX';
      $defaultOptions = $this->mkDefaultOptions();
      $this->optionName = $this->prefix . get_option('stylesheet');
      $this->options = get_option($this->optionName);
      if (empty($this->options)) {
        $this->options = $defaultOptions;
      }
      else {
        $this->options = array_merge($defaultOptions, $this->options);
      }
    }

    function mkDefaultOptions() {
      $defaultOptions = array(
          'text_color' => '00000',
          'bg_color' => 'FFFFFF',
          'tag' => 'math',
          'size' => '0') +
              parent::mkDefaultOptions();
      return $defaultOptions;
    }

    function mkFormulaURL($text = '', $size = '') {
      if (empty($size)) {
        $size = $this->options['size'];
      }
      if (empty($text)) {
        $text = "(a+b)^2 = a^2 + b^2 + 2ab";
      }
      $text = rawurlencode($text);
      $url = $this->server . htmlspecialchars("?latex=$text&bg=") . "{$this->options['bg_color']}&fg={$this->options['text_color']}&s=$size";
      return $url;
    }

    function mkSizeLabel($label, $size) {
      $url = $this->mkFormulaURL('', $size);
      $img = "<span style='width:70px;display:inline-block'>$label</span><img style='vertical-align:-40%;' alt='Formula @ size=$size' src='$url' />";
      return $img;
    }

    function mkHelpTags() {
      $this->helpTags = array();
      $o = new EzHelpTag('help0');
      $o->title = __('Click for help', 'easy-latex');
      $o->tipTitle = __('How to use this plugin', 'easy-latex');
      $o->desc = __('How to use this plugin', 'easy-latex');
      $this->helpTags[] = $o;

      $o = new EzHelpTag('help1');
      $o->title = __('Click for help', 'easy-latex');
      $o->tipTitle = __('How to tweak the displayed formulas', 'easy-latex');
      $o->desc = __('Need to control how the formulas appear?', 'easy-latex');
      $this->helpTags[] = $o;

      $o = new EzHelpTag('help2');
      $o->title = __('Click for help', 'easy-latex');
      $o->tipTitle = __('How to set up the plugin', 'easy-latex');
      $o->desc = __('Change colors, tags, formula font sizes etc.', 'easy-latex');
      $this->helpTags[] = $o;

      $o = new EzHelpPopUp('http://wordpress.org/extend/plugins/easy-wp-latex-lite/');
      $o->title = __('Click for help', 'easy-latex');
      $o->desc = __('Check out the FAQ and rate this plugin.', 'easy-latex');
      $this->helpTags[] = $o;
    }

    function mkEzOptions() {
      if (!empty($this->ezOptions)) {
        return;
      }

      parent::mkEzOptions();

      $o = new EzColorPicker('text_color');
      $o->desc = __("Text Color: ", 'easy-latex');
      $o->labelWidth = '220px';
      $o->style = 'width:70px;float:right';
      $o->after = '<br />';
      $this->ezOptions['text_color'] = clone $o;

      $o = new EzColorPicker('bg_color');
      $o->desc = __("Background Color: ", 'easy-latex');
      $o->labelWidth = '220px';
      $o->style = 'width:70px;float:right';
      $o->after = '<br /><br />';
      $this->ezOptions['bg_color'] = clone $o;

      $o = new EzRadioBox('tag');
      $o->desc = "<br /><b>" . __('Bracketting Tags', 'easy-latex') . "</b><br />";
      $o->title = __('Select how you want to bracket your equations.', 'easy-latex');
      $o->addChoice('math', 'math', __('[math] ... [/math] phpBB Style', 'easy-latex'))->after = "<br />";
      $o->addChoice('latex', 'latex', __('$$ ... $$  LaTeX Style', 'easy-latex'))->after = "<br />";
      $o->addChoice('mtype', 'mtype', __('\[ ... \] MathType Style', 'easy-latex'));
      $o->after = "<br />";
      $this->ezOptions['tag'] = clone $o;

      $o = new EzRadioBox('size');
      $o->desc = "<b>" . __('LaTeX Equation Font Size', 'easy-latex') . "</b>";
      $o->title = __('Choose the size of the LaTeX equations to match your font size', 'easy-latex');
      $o->addChoice('0', '0', $this->mkSizeLabel(__('Small', 'easy-latex'), 0))->after = "<br /><br />";
      $o->addChoice('1', '1', $this->mkSizeLabel(__('Medium', 'easy-latex'), 1))->after = "<br /><br />";
      $o->addChoice('2', '2', $this->mkSizeLabel(__('Large', 'easy-latex'), 2))->after = "<br /><br />";
      $o->addChoice('3', '3', $this->mkSizeLabel(__('X-Large', 'easy-latex'), 3))->after = "<br /><br />";
      $o->addChoice('4', '4', $this->mkSizeLabel(__('XX-Large', 'easy-latex'), 4))->after = "<br /><br />";
      $o->after = "<br />";
      $this->ezOptions['size'] = clone $o;
    }

    //Prints out the admin page
    function printAdminPage() {
      $ez = parent::printAdminPage();
      if (empty($ez)) {
        return;
      }
      $this->handleSubmits();
      echo $this->adminMsg;

      $this->mkEzOptions();
      $this->setOptionValues();
      $this->mkHelpTags();

      echo '<script type="text/javascript" src="' . $this->plgURL . '/ezColor/jscolor.js"></script>';
      echo '<script type="text/javascript" src="' . $this->plgURL . '/wz_tooltip.js"></script>';
      ?>

      <div class="wrap" style="width:1000px">
        <h2>Easy WP LaTeX <?php echo $this->strPro; ?> Setup
          <a href="http://validator.w3.org/" target="_blank"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" title="Easy AdSense Admin Page is certified Valid XHTML 1.0 Transitional" height="31" width="88" class="alignright"/></a>
        </h2>

        <table style="width:100%">
          <tr style="vertical-align:top">
            <td style="width:40%">
              <h3>
                <?php
                _e('Instructions', 'easy-latex');
                echo "</h3>\n<ul style='padding-left:10px;list-style-type:circle; list-style-position:inside;'>\n";
                foreach ($this->helpTags as $help) {
                  echo "<li>";
                  $help->render();
                  echo "</li>\n";
                }
                ?>
                </ul>
            </td>

            <?php include ($this->plgDir . '/head-text.php'); ?>

          </tr>
        </table>

        <h3>
          <?php
          printf(__('Options (for the %s theme)', 'easy-latex'), get_option('stylesheet'));
          ?>
        </h3>
        <form method="post" action="#">
          <?php
          $this->renderNonce();
          $ez->renderNags($this->options);
          ?>
          <table>
            <tr style="vertical-align:top">
              <td style="width:50%">
                <?php
                echo "<b>" . __("Color Configuration", 'easy-latex') .
                "</b><br /><br />";
                $this->ezOptions['text_color']->render();
                $this->ezOptions['bg_color']->render();
                $this->ezOptions['tag']->render();
                ?>
              </td>
              <td>
                <?php
                $this->ezOptions['size']->render();
                ?>
              </td>
            </tr>
            <tr>
              <td>
                <br /><b><?php _e("Other Options", 'easy-latex'); ?></b><br />
                <?php $this->ezOptions['kill_author']->render(); ?>
              </td>
              <td>
                <?php
                $ez->renderWhyPro($short = true);
                ?>
              </td>
            </tr>
          </table>

          <div class="submit">
            <?php
            $this->renderSubmitButtons();
            $this->ezTran->renderTranslator();
            ?>
          </div>
          <br />
        </form>
        <?php
        $ez->renderWhyPro();
        $ez->renderSupport();
        include ($this->plgDir . '/tail-text.php');
        ?>

        <table class="form-table" >
          <tr><th scope="row"><b><?php _e('Credits', 'easy-latex'); ?></b></th></tr>
          <tr><td>
              <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >
                <li>
                  <?php
                  printf("%s is based on <b>Latex for WordPess</b> by zhiqiang, and shares some features and core engine code with it.", "<b>Easy WP LaTeX $this->strPro</b>");
                  ?>
                </li>
                <li>
                  <?php printf(__('%s uses the excellent Javascript/DHTML tooltips by %s', 'easy-latex'), '<b>Easy WP LaTeX' . $this->strPro . '</b>', '<a href="http://www.walterzorn.com" target="_blank" title="Javascript, DTML Tooltips"> Walter Zorn</a>.');
                  ?>
                </li>
              </ul>
            </td>
          </tr>
        </table>
        <div id="help0" style='display:none'>
          <ul>
            <?php
            echo "<li>";
            _e('Enter LaTeX formulas wherever you need in your posts or pages.', 'easy-latex');
            echo "</li><li>";
            _e('Bracket each one of your LaTeX formulas with the tags you specify in the option below. The default tag is <code>[math][/math].</code> For example, type in <br /><code>[math](a+b)^2 = a^2 + b^2 + 2ab[/math]</code> and you will get:', 'easy-latex');
            echo $this->mkSizeLabel('', '1');
            echo "</li>";
            ?>
          </ul>
        </div>

        <div id="help1" style='display:none'>
          <ul>
            <?php
            echo "<li>";
            _e('Use the exclamation mark as the first character to generate a displayed equation (i.e., centered, on its own line): <code>[math]!(a+b)^2[/math]</code>', 'easy-latex');
            echo "</li><li>";
            _e('Use the exclamation mark as the last character to suppress formula output: <code>[math](a+b)^2![/math]</code>.', 'easy-latex');
            echo "</li>";
            ?>
          </ul>
        </div>

        <div id="help2" style='display:none'>
          <ul>
            <?php
            echo "<li>";
            _e('Use the color pickers to under Color Configuration to match the forumula background and forground colors to your theme colors. The settings will be stored for each theme, so that if you return to an old theme, you do not have to re-configure the colors.', 'easy-latex');
            echo "</li><li>";
            _e('The plugin can use different tags to identify your formula text. By default, it will look for segements enclosed in <code>[math]</code> and <code>[/math]</code> and render them as LaTeX formulas. If you prefer other bracketting tags, use the option below.', 'easy-latex');
            echo "</li><li>";
            _e('The size of the formulas can be tweaked to match the text size on your pages. Five sizes are supported as shown below.', 'easy-latex');
            echo "</li>";
            ?>
          </ul>
        </div>

      </div>

      <?php
    }

    function parseTex($toParse) {
      // tag specification (which tags are to be replaced)
      $tag = $this->options['tag'];
      // $regex = '#' . $tag1 . '  *(.*?)' . $tag2 . '#si';
      $regex = '#\[math\] *(.*?)\[/math\]#si';
      if ($tag == 'latex') {
        $regex = '#\$\$(.*?)\$\$#si';
      }
      if ($tag == 'mtype') {
        $regex = '#\\\[(.*?)\\\]#si';
      }
      return preg_replace_callback($regex, array($this, 'createTex'), $toParse);
    }

    function createTex($toTex) {
      // clean up <br /> and other junk
      $formula_text = str_replace(array("\r\n", "\n", "\r"), "", $toTex[1]);
      $imgtext = false;

      if (substr($formula_text, -1, 1) == "!") {
        return "$$" . substr($formula_text, 0, -1) . "$$";
      }

      if (substr($formula_text, 0, 1) == "!") {
        $imgtext = true;
        $formula_text = substr($formula_text, 1);
      }

      $formula_url = $this->mkFormulaURL($formula_text);


      $style = "style='vertical-align:1%'";
      $formula_output = "<img src='$formula_url' title='$formula_text' $style class='tex' alt='$formula_text' />";

      if ($imgtext) {
        return '<center>' . $formula_output . '</center>';
      }

      return $formula_output;
    }

    function plugin_action($links, $file) {
      if ($file == plugin_basename($this->plgDir . '/easy-latex.php')) {
        $settings_link = "<a href='options-general.php?page=easy-latex.php'>Settings</a>";
        array_unshift($links, $settings_link);
      }
      return $links;
    }

  }

} //End Class ezLaTeX

if (class_exists("EzLaTeX")) {
  $ezLaTeX = new EzLaTeX();
  if (isset($ezLaTeX)) {
    if (!function_exists("ezLaTeX_ap")) {

      function ezLaTeX_ap() {
        global $ezLaTeX;
        if (function_exists('add_options_page')) {
          $mName = 'Easy WP LaTeX';
          add_options_page($mName, $mName, 'activate_plugins', basename(__FILE__), array($ezLaTeX, 'printAdminPage'));
        }
      }

    }
    add_filter('the_title', array($ezLaTeX, 'parseTex'), 1);
    add_filter('the_content', array($ezLaTeX, 'parseTex'), 1);
    add_filter('the_excerpt', array($ezLaTeX, 'parseTex'), 1);
    add_filter('comment_text', array($ezLaTeX, 'parseTex'), 1);

    add_action('admin_menu', 'ezLaTeX_ap');
    add_filter('plugin_action_links', array($ezLaTeX, 'plugin_action'), -10, 2);
  }
}
