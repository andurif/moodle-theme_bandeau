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

/**
 * Custom renderers (child of Boost theme renderer) to display our banner.
 *
 * @package    theme_bandeau
 * @author     Anthony Durif - Université Clermont Auvergne
 * @copyright  2019 Anthony Durif - Université Clermont Auvergne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_bandeau\output;

use moodle_url;
use theme_boost\output\core_renderer as boost_renderer;
use core_course\external\course_summary_exporter;

defined('MOODLE_INTERNAL') || die;

/**
 * Custom renderers (child of Boost theme renderer) to display our banner.
 *
 * @package    theme_bandeau
 * @author     Anthony Durif - Université Clermont Auvergne
 * @copyright  2019 Anthony Durif - Université Clermont Auvergne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends boost_renderer
{
    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $USER;

        $this->filter_drawer();
        $courseurl = new moodle_url('/course/view.php', array('id' => $this->page->course->id));

        $params = [
            'context_header_settings_menu' => (is_primary_admin($USER->id) ? $this->context_header_settings_menu() : false),
            'context_header' => $this->context_header(),
            'navbar' => (empty($this->page->layout_options['nonavbar']) ? $this->navbar() : false),
            'page_heading_button' => str_replace('btn-secondary', 'btn-primary', $this->page_heading_button()),
            'course_header' => $this->course_header(),
            'tools' => $this->header_tools(),
            'course_image' => $this->get_course_image(),
            'has_links' => $this->has_links(),
            'courseurl' => $courseurl->out(),
        ];

        return $this->render_from_template('theme_bandeau/page-header', $params);
    }

    /**
     * Function to get HTML of the banner tool.
     *
     * @return bool|string
     */
    public function header_tools() {
        if ($this->page->course->id == 1) {
            return false;
        }

        $links = [];
        $contents = [];

        // By default, we also call others functions called xxx_render_page_header_output() if there is some.
        // Warning: call these functions can cause a display conflict.
        if ($pluginsfunction = get_plugins_with_function('render_page_header_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    if ($pluginfunction()['contents'] != null && $pluginfunction()['links'] != null) {
                        $links = array_merge($links, $pluginfunction()['links']);
                        $contents = array_merge($contents, $pluginfunction()['contents']);
                    }
                }
            }
        }
        // If you just want call the theme's function, comment the previous if section and uncomment the code section below.
        /*$output = theme_bandeau_render_page_header_output();
        $links = $output['links'];
        $contents = $output['contents'];*/

        if ($this->has_links()) {
            foreach ($links as $index => $link) {
                $links[$index]->index = $index;
                $contents[$index]->index = $index;
            }
        } else {
            $links = [];
        }

        $courseurl = new moodle_url('/course/view.php', array('id' => $this->page->course->id));

        return $this->render_from_template('theme_bandeau/page-header-tools', [
            "coursename" => $this->page->course->fullname,
            "courseurl" => $courseurl->out(),
            "links" => $links,
            "contents" => $contents,
        ]);
    }

    /**
     *  Shows only selected elements in the drawer
     */
    protected function filter_drawer() {
        if ($this->page->course->id > 1) {
            foreach ($this->page->flatnav->get_key_list() as $key) {
                $element = $this->page->flatnav->get($key);
                if ((true !== $element->is_section()) && ('coursehome' != $key) && ('addblock' != $key)) {
                    $this->page->flatnav->remove($key);
                    continue;
                }
            }
        } else {
            if (isset($this->page->flatnav->get('mycourses')->children)) {
                foreach ($this->page->flatnav->get('mycourses')->children as $child) {
                    $this->page->flatnav->remove($child->key);
                }
                $this->page->flatnav->remove('mycourses');
            }
            $this->page->flatnav->remove('home');
        }
    }

    /**
     * Function to return the course image displayed in the course header.
     * @return mixed|string|null
     * @throws \dml_exception
     */
    public function get_course_image() {
        if ($this->page->course->id == 1) {
            return null;
        }

        $image = (class_exists(course_summary_exporter::class) && method_exists(course_summary_exporter::class, 'get_course_image'))
            ? course_summary_exporter::get_course_image(get_course($this->page->course->id)) : null;
        $image = (!$image) ? get_config('theme_bandeau', 'default_course_img') : $image;

        return $image;
    }

    /**
     * Get the course pattern datauri to show on a course card.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param int $id Course id to use when generating the pattern
     * @return string datauri
     */
    public function get_generated_image_for_id($id) {
        try {
            return (get_config('theme_bandeau', 'default_course_img') != '')
                ? (new moodle_url(get_config('theme_bandeau', 'default_course_img')))->out()
                : parent::get_generated_image_for_id($id);
        } catch (\Exception $e) {
            parent::get_generated_image_for_id($id);
        }
    }

    /**
     * Check if the user has manage links in the header.
     *
     * @return boolean true if the user has manage links and false in other cases.
     */
    public function has_links() {
        if ($this->page->course->id == 1) {
            return false;
        }

        $output = theme_bandeau_render_page_header_output();
        $links = $output['links'];

        // If the user has only one link we will consider like he has none because the course homepage link is always present.
        return (count($links) > 1);
    }

    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     * We do not display the button (visible since 3.9 version) because we display it in the actions header bar.
     * @param moodle_url $url
     * @return string
     */
    public function edit_button(moodle_url $url) {
        return '';
    }

    /**
     * Returns course-specific information to be output immediately above content on any course page
     * (for the current course)
     *
     * @param bool $onlyifnotcalledbefore output content only if it has not been output before
     * @return string
     */
    public function course_content_header($onlyifnotcalledbefore = false) {
        if (strpos($this->page->url->get_path(), "contentbank/view") !== false) {
            return "<div style='margin-left: 50px; margin-bottom: 20px;'>" . $this->page->get_header_actions()[0] . "</div>" . parent::course_content_header($onlyifnotcalledbefore) . "<br/><br/>";
        }

        return parent::course_content_header($onlyifnotcalledbefore);
    }
}

