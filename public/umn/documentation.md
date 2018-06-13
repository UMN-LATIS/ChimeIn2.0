## Sass-Specific—Header & Footer Use Documentation
** Requires Sass version 3.3 or greater **

The Sass version of the header and footer has been coded to allow you to customize it to your preferences. These customizations are specified in a config file along with a few optional edits to the 2015-tc.html file. 

If you have not used Sass you can learn more about it here:

[http://sass-lang.com/](http://sass-lang.com/)

## Files

In the sass directory

### Delete These
**In root directory:**

- /morris
- /duluth

**In /sass:**

- 2015-d-alt.scss- 2015-d-t.scss- 2015-mo.scss
- /duluth
- /morris

### Keep These
**In /sass:**

- 2015-tc.scss - Twin Cities Campus header/footer stylesheet
- /helpers - this directory contains definitions for colors and various mixins
- /partials - this directory contains styles for the various elements in the header and footer
- /twin-cities - this directory contains the Twin Cities _config.scss file where you may adjust various settings and elements.

##_config.scss
This file allows you to set the various options for your header and footer. You may find a sample _config.scss at the end of this document.

The following are the various settings you may choose from and any requirements associated with each. Any optional elements are noted as such.

You may find the config file for the Twin Cities Header and Footer in

> sass/twin-cities/_config.scss

### Header Color
This setting simply determines whether your header will be maroon or gold. Use the provided variables ($maroon or &gold) to set this value as they include the approved colors.

The wordmark, MyU lock icon, and search icon will update automatically based on the color you choose.

### Driven to Discover
This option allows you to choose a wordmark variant. If set to `true` your wordmark will include the words "Driven to Discover". If set to `false` "Driven to Discover" will be omitted. 

### Max Width
This value sets the maximum width for your site. The default value is `1152px`. You may also choose `960px` or a custom pixel width of your choosing. It is recommended to not use a width less than `960px`.

### Header Search Style
This determines the behavior of the search bar on small screens. 

If this value is set to `true` the search bar will collapse into a "Search" button. When a user clicks this button, the search bar will appear below the wordmark.

If this value is set to `false` the search bar will flow below the wordmark without the need for a click. The "Search" button will remain hidden.

### Unit Identification and Navigation
It is not mandatory to use the unit identification and navigation in your site. If you choose to use it you may choose from two link orientations. 

1. Horizontal:
	```
	$vertical: false;
	```
2. Vertical
	```
	$vertical: true;
	```
You should have no more than eight and no fewer than four links in this section.
	
If you choose not to use the unit identification and navigation remove this section from the 2015-tc.html file and remove the `$vertical` variable from your _config.scss file. This will omit all unit identification and navigation CSS from the compiled CSS file.

### Unit Footer

It is not mandatory to use the unit footer in your site. You may remove it completely or develop your own version if you wish. 

If you choose the provided version, you must follow the following requirements. 

1. Social media links must remain beneath your unit contact information. The icons must be `16px` by `16px`.
2. You may tailor the links on the right to your unit's needs. You may not exceed four links in this section

If you do not wish to use the unit footer remove the unit footer section from the 2015-tc.html file and set the `$unit_f` variable to `false`.

### Footer

The footer does not have any associated settings. However, the "Current as of" section is optional and may be removed if you do not wish to use it.

### Complete _config.scss Example

This _config.scss file will export the css for a header and footer with the following styles:

- Maroon header
- UofM logo includes 'Driven to Discover'
- A maximum width of 1152 pixels
- The search bar is hidden at first on mobile devices and drops down when users click the 'Search' button
- The unit identification and navigation is included and is in the vertical, two-column orientation
- The unit footer is included

```Sass
//Use the options below to customize your header and footer. 

// HEADER COLOR
// Determines whether the header is maroon or gold.
// Options:
// 	   $maroon
//     $gold
$header_color: $maroon;

// DRIVEN TO DISCOVER
// Use logo with 'Driven to Discover' text?
//		true => Include 'Driven to Discover'
// 		false => Do not include 'Driven to Discover'
$driven: true;

// MAX WIDTH
// Choose between two possible maximum widths:
// 		960px
// 		1152px
$max_width: 1152px;

// HEADER SEARCH STYLE
// Dropdown: The search bar will collapse into a "Search" button on mobile devices. 
// Two-Tiered: The search bar will drop below the University of Minnesota logo on mobile devices.
// 		true => Dropdown
// 		false => Two-Tiered
$dropdown: true;

// UNIT IDENTIFICATION AND NAVIGATION
// Chooose to include styles for the unit identification and navigation or not.
// Remove this section from your template if you do not wish to use this section
// 		true => Vertical
// 		false => Horizontal
$vertical: true;

// UNIT FOOTER
// Choose to include the styles for the unit footer or not.
// If false, remove this section from your template.
// 		true => Will include styles for the unit footer
// 		false => Will not include styles for the unit footer
$unit_f: true;
```

## Clean Up

### Comments
Review the 2015-tc.html file and remove instructional comments. Many regions are denoted as "optional" along with some additional notes. Remove these comments if you intend on using any optional regions. 