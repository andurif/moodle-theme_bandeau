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
 * Theme Bandeau functions.
 *
 * @package    theme_bandeau
 * @author     Anthony Durif - Université Clermont Auvergne
 * @copyright  2019 Anthony Durif - Université Clermont Auvergne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_bandeau_get_pre_scss($theme) {
    // Load the settings from the parent.
    $theme = theme_config::load('boost');
    // Call the parent themes get_pre_scss function.
    return theme_boost_get_pre_scss($theme);
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_bandeau_get_extra_scss($theme) {
    // Load the settings from the parent.
    $theme = theme_config::load('boost');
    // Call the parent themes get_extra_scss function.
    return theme_boost_get_extra_scss($theme);
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_bandeau_get_main_scss_content($theme) {
    global $CFG;

    $scss = "";
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_bandeau', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_bandeau and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/bandeau/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/bandeau/scss/post.scss');

    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post;
}

/**
 * Returns the links to display in the banner.
 *
 * @param array $conf Array with links informations.
 * @return array
 */
function theme_bandeau_get_links_html($conf) {
    $links = [];
    $contents = [];

    if (isset($conf)) {
        foreach ($conf as $element) {
            $link = new \StdClass();
            $link->href = "";

            if (isset($element["link"]["edit"])) {
                $link->content = $element["link"]["label"];
                $link->image = "t/edit_mode_".$element["link"]["edit"];
                $link->class = "edit_mode ".$element["link"]["edit"];
            } else {
                $link->content = "<i class=\"material-icons\">" . $element["link"]["icon"] . "</i> " . $element["link"]["label"];
            }

            if (isset($element["home"]) && $element["home"]) {
                $link->class = "home-link";
            }
            $link->title = (isset($element["link"]["title"])) ? $element["link"]["title"] : $element["link"]["label"];

            if (null == $element["content"]) {
                // Unique link (no submenu).
                $link->direct = true;
                $link->href = $element["link"]["url"];
                if (isset($element["link"]["edit"])) {
                    $link->edit = true;
                }
            }

            $links[] = $link;

            $content = new \StdClass();
            $content->content = ($element["content"]["text"] != "")
                ? '<blockquote>' . $element["content"]["text"] . '</blockquote>' : "";
            if (isset($element["content"]["categories"])) {
                $content->content .= "<div class='row'>";
                foreach ($element["content"]["categories"] as $category) {
                    $content->content .= "<div class='col'>
                        <div class='row mb-2'>
                            <div class='col-2 subcategory_title'>
                                <strong>
                                    <i class=\"material-icons\">" . $category["icon"] . "</i> " . $category["label"] . "
                                </strong>
                            </div>
                            <div class='col'>";
                    foreach ($category["links"] as $link) {
                        $content->content .= "<div class='col'>
                            <a href='" . $link["url"] . "'>
                                <i class=\"material-icons\">" . $link["icon"] . "</i> " . $link["label"] . "
                            </a>
                        </div>";
                    }
                    $content->content .= "</div></div></div>";
                }
                $content->content .= "</div>";
            }

            if (isset($element["content"]["links"])) {
                foreach ($element["content"]["links"] as $link) {
                    $content->content .= "<a href='" . $link["url"] . "' class='mr-4'>
                        <i class=\"material-icons\">" . $link["icon"] . "</i> " . $link["label"] . "
                    </a>";
                }
            }

            $contents[] = $content;
        }
    }

    return ["links" => $links, "contents" => $contents];
}

/**
 * Function to build the hierarchy of links we want to display in the block banner.
 *
 * @return array
 */
function theme_bandeau_build_header_links() {
    global $COURSE, $DB, $PAGE, $USER, $CFG, $SESSION;

    $links = [];
    // Main items build.
    $links["manage"]["title"] = [
        "icon" => "settings",
        "label" => get_string("manage", "theme_bandeau")
    ];
    $links["manage"]["categories"] = [];
    $links["users"]["title"] = [
        "icon" => "person",
        "label" => get_string("users")
    ];
    $links["users"]["categories"] = [];
    $links["rapport"]["title"] = [
        "icon" => "equalizer",
        "label" => get_string("report")
    ];

    // We always display the homepage course link.
    $links["home"]["title"] = [
        "icon" => "school",
        "label" => false,
        "title" => get_string("home_course", "theme_bandeau"),
        "url" => new moodle_url('/course/view.php', ['id' => $COURSE->id])
    ];

    if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
        $links["manage"]["categories"]["params"] = [
            "icon" => "settings",
            "label" => get_string("editsettings"),
            "links" => []
        ];
        $links["manage"]["categories"]["params"]["links"][] = [
            "icon" => "",
            "label" => get_string("edit_params", "theme_bandeau"),
            "url" => new moodle_url('/course/edit.php', ['id' => $COURSE->id])
        ];
        $links["manage"]["categories"]["params"]["links"][] = [
            "icon" => "",
            "label" => get_string("competencies", "core_competency"),
            "url" => new moodle_url('/admin/tool/lp/coursecompetencies.php', ['courseid' => $COURSE->id])
        ];

        // Edit mode use.
        if ($PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
            // We are on the course page, retain the current page params e.g. section.
            $baseurl = clone($PAGE->url);
            $baseurl->param('sesskey', sesskey());
        } else {
            // Edit on the main course page.
            $baseurl = new moodle_url('/course/view.php', [
                'id' => $COURSE->id,
                'return' => $PAGE->url->out_as_local_url(false),
                'sesskey' => sesskey()
            ]);
        }
        $editurl = clone($baseurl);
        $blockurl = null;
        if ($PAGE->user_is_editing()) {
            $editurl->param('edit', 'off');
            $blockurl = new moodle_url('/course/view.php', [
                'id' => $COURSE->id,
                'bui_addblock' => '',
                'sesskey' => sesskey()
            ]);
        } else {
            $editurl->param('edit', 'on');
        }
        $links["edit_mode"]["title"] = [
            "icon" => "create",
            "label" => get_string('edit_mode', 'theme_bandeau'),
            "url" => $editurl,
            "edit" => $editurl->get_param('edit')
        ];
        if ($PAGE->user_can_edit_blocks() && isset($blockurl)) {
            // We also a button to allow to add a new block if the edit mode is on.
            $links["add_block"]["title"] = [
                "icon" => "add_box",
                "label" => get_string('addblock'),
                "url" => $blockurl
            ];
        }

        $links["admin"]["title"] = [
            "icon" => "build",
            "label" => get_string("morenavigationlinks"),
            "url" => new moodle_url('/course/admin.php', ['courseid' => $COURSE->id])
        ];
    }

    if (has_capability('moodle/course:delete', context_course::instance($COURSE->id))) {
        $links["manage"]["categories"]["params"]["links"][] = [
            "icon" => "",
            "label" => get_string("delete_course", "theme_bandeau"),
            "url" => new moodle_url('/course/delete.php', ['id' => $COURSE->id])
        ];
    }

    if (has_capability('moodle/backup:backupcourse', context_course::instance($COURSE->id))
        || has_capability('tool/recyclebin:viewitems', context_course::instance($COURSE->id))) {
        $links["manage"]["categories"]["import"] = [
            "icon" => "import_export",
            "label" => get_string("import_export", "theme_bandeau"),
            "links" => []
        ];
        if (has_capability('moodle/backup:backupcourse', context_course::instance($COURSE->id))) {
            $links["manage"]["categories"]["import"]["links"][] = [
                "icon" => "",
                "url" => new moodle_url('/backup/backup.php', ['id' => $COURSE->id]),
                "label" => get_string("backup")
            ];
            $links["manage"]["categories"]["import"]["links"][] = [
                "icon" => "",
                "url" => new moodle_url('/backup/restorefile.php?', ['contextid' => context_course::instance($COURSE->id)->id]),
                "label" => get_string("restore")
            ];
            $links["manage"]["categories"]["import"]["links"][] = [
                "icon" => "",
                "url" => new moodle_url('/backup/import.php', ['id' => $COURSE->id]),
                "label" => get_string("import")
            ];
        }
        if (has_capability('tool/recyclebin:viewitems', context_course::instance($COURSE->id))) {
            $links["manage"]["categories"]["import"]["links"][] = [
                "icon" => "",
                "url" => new moodle_url('/admin/tool/recyclebin/index.php', [
                    'contextid' => context_course::instance($COURSE->id)->id
                ]),
                "label" => get_string("pluginname", "tool_recyclebin")
            ];
        }
    }

    // Sub items build.
    if (has_capability('enrol/manual:enrol', context_course::instance($COURSE->id))
        ||  has_capability('moodle/course:enrolreview', context_course::instance($COURSE->id))) {
        $links["users"]["categories"]["users"] = [
            "icon" => "person",
            "label" => get_string("users"), "links" => []
        ];
    }

    if (has_capability('moodle/course:enrolreview', context_course::instance($COURSE->id))) {
        // Optionnal: set lastname as sort criteria, if not necessary delete this parameters in the next moodle_url() call.
        $sortby = isset($SESSION->flextable) ? current($SESSION->flextable)['sortby'] : null;
        $prefsort = isset($sortby) ? key($sortby) : 'lastname';
        $links["users"]["categories"]["users"]["links"][] = [
            "icon" => "",
            "url" => new moodle_url('/user/index.php', ['id' => $COURSE->id, 'tsort' => $prefsort]),
            "label" => get_string("enrolledusers", "core_enrol")
        ];
    }

    if (has_capability('enrol/manual:enrol', context_course::instance($COURSE->id))) {
        $enrol = $DB->get_record('enrol', array('courseid' => $COURSE->id, "enrol" => "manual", 'status' => 0), '*');
        if ($enrol) {
            // We check that manual enrol is enable in the enrolment methods of the course.
            $links["users"]["categories"]["users"]["links"][] = [
                "icon" => "",
                "url" => new moodle_url('/enrol/manual/manage.php', ['enrolid' => $enrol->id]),
                "label" => get_string("pluginname", "enrol_manual")
            ];
        }
        $links["users"]["categories"]["users"]["links"][]  = [
            "icon" => "",
            "url" => new moodle_url('/enrol/editinstance.php', ['courseid' => $COURSE->id, "type" => "cohort"]),
            "label" => get_string("enrol_cohort", "theme_bandeau")
        ];
        $enrol = $DB->get_record('enrol', array('courseid' => $COURSE->id, "enrol" => "self"), 'id');
        $links["users"]["categories"]["users"]["links"][]  = [
            "icon" => "",
            "url" => new moodle_url('/enrol/editinstance.php', ['courseid' => $COURSE->id, "id" => $enrol->id, "type" => "self"]),
            "label" => get_string("pluginname", "enrol_self")
        ];
    }

    if (has_capability('moodle/course:managegroups', context_course::instance($COURSE->id))) {
        $links["users"]["categories"]["groups"] = [
            "icon" => "group",
            "label" => get_string("groups"),
            "links" => []
        ];
        $links["users"]["categories"]["groups"]["links"][] = [
            "icon" => "",
            "url" => new moodle_url('/group/index.php', ['id' => $COURSE->id]),
            "label" => get_string("groups")
        ];
        $links["users"]["categories"]["groups"]["links"][] = [
            "icon" => "",
            "url" => new moodle_url('/group/groupings.php', ['id' => $COURSE->id]),
            "label" => get_string("groupings", "core_group")
        ];
        $links["users"]["categories"]["groups"]["links"][] = [
            "icon" => "",
            "url" => new moodle_url('/group/overview.php', ['id' => $COURSE->id]),
            "label" => get_string("overview", "core_group")
        ];
    }

    if (has_capability('moodle/question:add', context_course::instance($COURSE->id))) {
        $links["questions"]["title"] = [
            "icon" => "storage",
            "label" => get_string("questionbank", "question"),
            "url" => new moodle_url('/question/edit.php', ['courseid' => $COURSE->id])
        ];
    }

    if (has_capability('report/log:view', context_course::instance($COURSE->id))) {
        $links["rapport"]["links"][] = [
            "icon" => "history",
            "url" => new moodle_url('/report/log/index.php', ['id' => $COURSE->id]),
            "label" => get_string("logs")
        ];
        $links["rapport"]["links"][] = [
            "icon" => "schedule",
            "url" => new moodle_url('/report/loglive/index.php', ['id' => $COURSE->id]),
            "label" => get_string("pluginname", "report_loglive")
        ];
        $links["rapport"]["links"][] = [
            "icon" => "schedule",
            "url" => new moodle_url('/report/participation/index.php', ['id' => $COURSE->id]),
            "label" => get_string("pluginname", "report_participation")
        ];
        $links["rapport"]["links"][] = [
            "icon" => "schedule",
            "url" => new moodle_url('/report/outline/index.php', ['id' => $COURSE->id]),
            "label" => get_string("pluginname", "report_outline")
        ];
        if (has_capability('moodle/competency:coursecompetencymanage', context_course::instance($COURSE->id))) {
            $links["rapport"]["links"][] = [
                "icon" => "check_box",
                "url" => new moodle_url('/report/competency/index.php', ['id' => $COURSE->id]),
                "label" => get_string("pluginname", "report_competency")
            ];
        }

        if (has_capability('gradereport/grader:view', context_course::instance($COURSE->id))) {
            $links["grades"]["title"] = [
                "icon" => "grid_on",
                "label" => get_string("gradebook", "core_admin"),
                "url" => new moodle_url('/grade/report/grader/index.php', ['id' => $COURSE->id])
            ];
        }

    }

    return $links;
}

/**
 * Function to sort items in the way we want to display them.
 *
 * @return array|null
 */
function theme_bandeau_render_page_header_output() {
    $items = ["home", "manage", "users", "questions", "grades", "rapport", "admin", "add_block", "edit_mode"];
    $links = theme_bandeau_build_header_links();
    $conf = [];

    foreach ($items as $item) {
        if (!empty($links[$item]["links"])) {
            $conf[] = [
                "link" => $links[$item]["title"],
                "content" => [
                    "text" => "",
                    "links" => $links[$item]["links"]
                ]
            ];
        } else {
            if (!empty($links[$item]["categories"])) {
                $conf[] = [
                    "link" => $links[$item]["title"],
                    "content" => [
                        "text" => "",
                        "categories" => $links[$item]["categories"]
                    ]
                ];
            } else {
                if (!empty($links[$item]["title"]["url"])) {
                    $conf[] = [
                        "link" => $links[$item]["title"],
                        "content" => null,
                        "home" => ($item === "home")
                    ];
                }
            }
        }
    }

    return theme_bandeau_get_links_html($conf);
}