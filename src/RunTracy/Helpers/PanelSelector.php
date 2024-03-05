<?php
/**
 * Copyright 2016 1f7.wizard@gmail.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace RunTracy\Helpers;

use Tracy\IBarPanel;

/**
 * Panel Selector panel
 * based on part of https://processwire.com/talk/topic/12208-tracy-debugger/
 */
class PanelSelector implements IBarPanel
{

    protected $icon;
    private $cfg;
    private $defcfg;

    public function __construct(array $cfg = [], array $defcfg = [])
    {
        $this->cfg = $cfg;
        $this->defcfg = $defcfg;
    }

    public function getTab()
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '.
            'version="1.1" x="0px" y="0px" width="16" height="16" viewBox="0 0 369.793 369.792" style="'.
            'enable-background:new 0 0 369.793 369.792;" xml:space="preserve"><path d="M320.83,140.434l-1.'.
            '759-0.627l-6.87-16.399l0.745-1.685c20.812-47.201,19.377-48.609,15.925-52.031L301.11,42.61 '.
            'c-1.135-1.126-3.128-1.918-4.846-1.918c-1.562,0-6.293,0-47.294,18.57L247.326,60l-16.916-6.812l'.
            '-0.679-1.684 C210.45,3.762,208.475,3.762,203.677,3.762h-39.205c-4.78,0-6.957,0-24.836,47.825l-'.
            '0.673,1.741l-16.828,6.86l-1.609-0.669 C92.774,47.819,76.57,41.886,72.346,41.886c-1.714,0-3.714'.
            ',0.769-4.854,1.892l-27.787,27.16 c-3.525,3.477-4.987,4.933,16.915,51.149l0.805,1.714l-6.881,16'.
            '.381l-1.684,0.651C0,159.715,0,161.556,0,166.474v38.418 c0,4.931,0,6.979,48.957,24.524l1.75,0.'.
            '618l6.882,16.333l-0.739,1.669c-20.812,47.223-19.492,48.501-15.949,52.025L68.62,327.18 c1.162,1'.
            '.117,3.173,1.915,4.888,1.915c1.552,0,6.272,0,47.3-18.561l1.643-0.769l16.927,6.846l0.658,1.693 '.
            'c19.293,47.726,21.275,47.726,26.076,47.726h39.217c4.924,0,6.966,0,24.859-47.857l0.667-1.742l16.'.
            '855-6.814l1.604,0.654     c27.729,11.733,43.925,17.654,48.122,17.654c1.699,0,3.717-0.745,4.876-'.
            '1.893l27.832-27.219     c3.501-3.495,4.96-4.924-16.981-51.096l-0.816-1.734l6.869-16.31l1.64-0.'.
            '643c48.938-18.981,48.938-20.831,48.938-25.755v-38.395     C369.793,159.95,369.793,157.914,320.83'.
            ',140.434z M184.896,247.203c-35.038,0-63.542-27.959-63.542-62.3     c0-34.342,28.505-62.264,63.'.
            '542-62.264c35.023,0,63.522,27.928,63.522,62.264C248.419,219.238,219.92,247.203,184.896,247.'.
            '203z" fill="#444444"/></svg>';

        return '
            <span title="Panel Selector">
                ' . $this->icon . '
            </span>';
    }


    public function getPanel()
    {
        $out = '
        <script>
            function getSelectedTracyPanelCheckboxes() {
                var selchbox = [];
                var inpfields = document.getElementsByName("selectedPanels[]");
                var nr_inpfields = inpfields.length;
                for(var i=0; i<nr_inpfields; i++) {
                    if(inpfields[i].checked == true) selchbox.push(inpfields[i].value);
                }
                return selchbox;
            }

            function changeTracyPanels(type) {
                document.cookie = "tracyPanels"+type+"="
                    +JSON.stringify( getSelectedTracyPanelCheckboxes() )+"; expires=0; path=/";
                location.reload();
            }

            function resetTracyPanels() {
                document.cookie = "tracyPanelsEnabled=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/";
                location.reload();
            }

            function disableTracy() {
                document.cookie = "tracyDisabled=1; expires=0; path=/";
                location.reload();
            }

            function toggleAllTracyPanels(ele) {
                var checkboxes = document.getElementsByName("selectedPanels[]");
                if (ele.checked) {
                    for (var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = true;
                        }
                    }
                }
                else {
                     for (var i = 0; i < checkboxes.length; i++) {
                        if(checkboxes[i].disabled === false) {
                            checkboxes[i].checked = false;
                        }
                    }
                }
            }
        </script>

        <style type="text/css">
            #tracy-debug-panel-RunTracy-Helpers-PanelSelector input[type="submit"] {
                background: #DDEFDD !important;
                padding: 3px !important;
                border: 1px solid #D2D2D2 !important;
                -webkit-border-radius: 5px !important;
                -moz-border-radius: 5px !important;
                border-radius: 5px !important;
                cursor: pointer !important;
            }
        </style>';

        $out .= '<h1>' . $this->icon . ' Panel Selector</h1>
        <div class="tracy-inner">
            <table width="100%">
                <thead><tr><th>*Panels with asterisk are on by default</th></tr></thead>
                    <tr><th><input type="checkbox" onchange="toggleAllTracyPanels(this)" /> Toggle All</th></tr>';
        foreach ($this->defcfg as $name => $val) {
            $out .= '<tr><td style="padding-left: 20px">
            <input type="checkbox" name="selectedPanels[]" ' .
                ((isset($this->cfg[$name]) && $this->cfg[$name] == 1) ? 'checked="checked"' : '') .
                ' value="' . $name . '" />&nbsp;' . $this->formatPanelName($name)
                . ($this->defcfg[$name] == 1 ? '&nbsp;<strong>*</strong>' : '') . '
            </td></tr>';
        }
        $out .= '
                <tfoot>
                    <tr>
                        <td style="text-align: center">
                            <input type="submit" onclick="changeTracyPanels(\'Enabled\')" value="Set" />&nbsp; &nbsp;
                            <input type="submit" onclick="resetTracyPanels()" value="Reset" /><br />
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>';

        return $out;
    }

    private function formatPanelName($name = '')
    {
        return preg_replace('/[A-Z]{1,3}/', ' \0', str_replace('show', '', $name));
    }
}
