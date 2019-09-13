<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace theme_bandeau\output;

use moodle_page;
use moodle_url;
use Mustache_Engine;
use theme_boost\output\core_renderer as boost_renderer;
use core_course\external\course_summary_exporter;

/**
 * Custom renderers (child of Boost theme renderer) to display our banner.
 *
 * @package    theme_bandeau
 * @copyright  2019 Université Clermont Auvergne, Anthony Durif
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends boost_renderer
{
    /** @var plugin_mustache */
    protected $plugin_mustache;

    /**
     * Constructor
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target = null)
    {
        parent::__construct($page, $target);
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE, $USER;

        $this->filter_drawer();

        //Course image display or not ?
        $image = course_summary_exporter::get_course_image(get_course($PAGE->course->id));
        $show_default = get_config('theme_bandeau', 'show_default_course_img');
        $image = (!$image) ? (($show_default) ? get_config('theme_bandeau', 'default_course_img') : null) : $image;

        $params = [
            'context_header_settings_menu' => (is_primary_admin($USER->id) ? $this->context_header_settings_menu() : false),
            'context_header' => $this->context_header(),
            'navbar' => (empty($PAGE->layout_options['nonavbar']) ? $this->navbar() : false),
            'page_heading_button' => str_replace('btn-secondary', 'btn-primary', $this->page_heading_button()),
            'course_header' => $this->course_header(),
            'tools' => $this->header_tools(),
            'course_image' => $image,
        ];
        
        return $this->render_from_template('theme_bandeau/page-header', $params);
    }

    /**
     * Function to get HTML of the banner tool.
     *
     * @return bool|string
     */
    public function header_tools()
    {
        global $PAGE, $USER;
        if ($PAGE->course->id == 1)
            return false;

        $links = [];
        $contents = [];

        if ($pluginsfunction = get_plugins_with_function('render_page_header_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    if($pluginfunction()['contents'] != null && $pluginfunction()['links'] != null) {
                        $links = array_merge($links, $pluginfunction()['links']);
                        $contents = array_merge($contents, $pluginfunction()['contents']);
                    }
                }
            }
        }

        foreach ($links as $index=>$link)
        {
            $links[$index]->index = $index;
            $contents[$index]->index = $index;
        }

        return $this->render_from_template('theme_bandeau/page-header-tools', [
            "coursename" => $PAGE->course->fullname,
            "links" => $links,
            "contents" => $contents,
            "is_admin" => is_primary_admin($USER->id)
        ]);
    }

    /**
     *  Shows only selected elements in the drawer
     */
    protected function filter_drawer()
    {
        global $PAGE;

        if ($PAGE->course->id > 1) {
            foreach ($PAGE->flatnav->get_key_list() as $key) {
                $element = $PAGE->flatnav->get($key);
                if ((true !== $element->is_section()) && ('coursehome' != $key) && ('addblock' != $key)) {
                    $PAGE->flatnav->remove($key);
                    continue;
                }
            }
        }
        else {
            if(isset($PAGE->flatnav->get('mycourses')->children)) {
                foreach ($PAGE->flatnav->get('mycourses')->children as $child) {
                    $PAGE->flatnav->remove($child->key);
                }
                $PAGE->flatnav->remove('mycourses');
            }
            $PAGE->flatnav->remove('home');
        }
    }
    
    /**
     * Get the course pattern datauri to show on a course card.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param int $id Id to use when generating the pattern
     * @return string datauri
     */
    public function get_generated_image_for_id($id)
    {
        try {
            return (get_config('theme_bandeau', 'default_course_img') != '')
                ? (new moodle_url(get_config('theme_bandeau', 'default_course_img')))->out() : parent::get_generated_image_for_id($id);
        }
        catch(\Exception $e) {
            parent::get_generated_image_for_id($id);
        }
    }
}