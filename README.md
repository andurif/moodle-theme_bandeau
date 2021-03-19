Bandeau Theme
==================================
Moodle theme and child of Boost theme. It will display courses recurrents actions as a banner in the course header block.

Goal
------------
The theme goal was to make course actions more available for users (and especially managers) et to pass on "edit mode" more intuitively. <br/>
It was also important for us to be able to display easily some personnal actions especially developped in the local/ folder without overload configuration menu ("engrenage" menu).

Presentation
------------
- Specific menu in the course header block. It displays possible recurrent actions on a course grouped by type of action. 
- Possibility to pass on "Editing mode" more easily.
- Possibility to keep menu on the top of the page even we scroll on the course.
- Possibility to add a block more easily without passing on the left menu.
- Possibility to return to course homepage more easily.
- Displays in the header block bakground the picture choosed in the course configuration (since moodle 3.6 version, in other cases it will display default image you define in the theme settings).
- Using of a default course picture in dashboard blocks (since moodle 3.7 version).

Requirements
------------
- Moodle 3.3 (build 2017051500) or later.<br/>
-> Tests on Moodle 3.3 to 3.10.1 versions (warning all features will maybe not be available in moodle 3.3.x to 3.5.x versions).<br/>
- Same other requirements than Boost theme.

Installation
------------
1. Local plugin installation

- Git way:
> git clone https://github.com/andurif/moodle-theme_bandeau.git theme/bandeau

- Download way:
> Download the zip from https://github.com/andurif/moodle-theme_bandeau/archive/master.zip unzip it in theme/ folder and rename it "bandeau" if necessary or install it from the "Install plugin" page if you have the right permissions..
  
2. Then visit your Admin Notifications page to complete the installation.

3. Once installed, you should see new administration options:

> Site administration -> Appearance -> Themes -> Bandeau settings -> default_course_img

This setting allows you to fix a picture url which will be used by default as background image in the course header block if no picture has been chosen in the course configuration.

> Site administration -> Appearance -> Themes -> Bandeau settings -> show_default_course_img

This setting permits to say if you want to use the default image if no picture has been chosen in the course configuration or not. If not checked only course with picture configured will display a course header block background.

How customize this theme ?
-----
For now we only use the most recurrent and available in the moodle core actions but it is possible to add some others.<br/>
For it, edit <i>lib.php</i> file and especially <i>theme_bandeau_build_header_links()</i> and <i>theme_bandeau_render_page_header_output()</i> functions.

- theme_bandeau_build_header_links()<br/>
In this function you need to build an array with links you want to display in the banner and in function of "types" or capabilities.<br/>
```php
<?php
/* Array structure to return */
$links = [
    "item1" => [  //Main item
        "title" => ["icon" => "cf_icon_material", "label" => "Label item1"],
        "categories" => [  //Subitems array
            "subitem1" => [  //subitem
                 "icon" => "cf_icon_material",
                 "label" => "Label subitem1",
                 "links" => [  //Links list
                    "icon" => "",
                    "label" => "Label link1",
                    "url" => "Link1 URL"
                 ]
            ]
        ]
    ],
    "item2" => [  //Main item
        "title" => ["icon" => "cf_icon_material", "label" => "Label item2"],
        "links" => [  //Subitem links array
            [  //Direct subitem
                "icon" => "cf_icon_material",
                "label" => "Label subitem1",
                "url" => "Subitem1 link"
            ]
        ]
    ],
    "item3" => [
        "title" => [  //Direct link
            "icon" => "",
            "label" => "Label direct link",
            "url" => "Direct link URL"
        ]
    ]
];
```

- theme_bandeau_render_page_header_output()<br/>
This function is used to sort previous array. The only update you need to do is define array items ($links_items var) in the order you want to display them in the banner. 

<strong>Be careful</strong>, if one of your additionnal plugin has a <i>xxx_render_page_header_output()</i> function a conflict can be possible during the banner display. 
To avoid conflicts it's possible to use only this theme function by commenting/uncommenting a section code starting on line 91 in the <i>classes/output/core_renderer.php</i> file.

Possible improvements
-----
- Use config file or admin settings to define menu items and avoid to directly update code if we want to change menu. Similarly to the configuration of the "Custom menu items" setting (settable by admins who are maybe not developpers).
- List all course enrolment methods in the "Users" item.
- Change theme to plugin which will be adaptable to any other theme.
- Use as many as possible heritage from Boost theme (provider, etc...)
- Improve accessibility (title...)
- Try to avoid conflicts if there is a xxx_render_page_header_output() function in another plugin.
- Find a better place for the Editing button in gradebook and content bank (not very visible according to course image).
- Bug: If the left menu is open and menu in "sticky" position the "Edit mode" button is no longer visible.
- Bug: If we scroll into the course and it is not very long, the banner can freeze.

About us
------
<a href="https://www.uca.fr">Université Clermont Auvergne</a> - 2020.<br/>
