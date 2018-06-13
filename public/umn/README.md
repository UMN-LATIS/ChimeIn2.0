# New Web Header and Footer
The responsive code for the new header and footer will be available here on March 30.

# Responsive Header and Footer Standards and Instructions
The responsive header and footer have been developed for units to use when building mobile or responsive websites.

**Note:** view the documentation.md file for specific use instructions.

If you do not wish to use the Sass version of the Header and Footer, visit the University Relations [Our Brand](https://www.ur.umn.edu/brand/) to download an alternate version.

## Standards
### Responsive header
The responsive header has been coded to display in three different ways, depending on the size of the device being used.

On screen sizes above 660 pixels, the responsive header includes the same features as the standard web header; the block M, wordmark and Driven to Discover combination, search, and MyU and One Stop links.

For screens between 520 and 660 pixels, all the header elements remain, but the search field becomes smaller to accommodate multiple screen sizes and still display all header elements.

For screen sizes below 520 pixels, the size of the block M, wordmark, and Driven to Discover have been reduced to the smallest size allowable by University logo standards. The links to One Stop and MyU will move to the footer on small screens.

#### Search
There are two options for search when the screen size is below 520 pixels. You may use whichever option works best for your site.

**Option one**-For this option, the header includes a search button that, when activated, displays a search field below the header. In this option, the search button moves down next to the search field.

**Option two**-This option removes the search button from the header. The search field is always shown and, on small screens, flows below the wordmark.

### Responsive Footer
The footer contains a shortened copyright and equal opportunity statement and a link to the University’s privacy policy. 

An optional "Current as of" line is also available. 

On small screens, the MyU and One Stop links move to the footer.

### Download and Branding Requirements
Downloads for the mobile header and footer graphics and code are available on the [Our Brand](https://www.ur.umn.edu/brand/) site or in the [University Relations GitHub repository](https://github.umn.edu/URel/HeaderFooter).

#### Central Hosting
Central hosting of assets is no longer offered.

#### Required
Do not alter the graphics or change the color of the block M, wordmark, Driven to Discover, or background.

## Technical Instructions
Refer to the documentation.md file for specific use instructions. 

If you do not wish to use the Sass version of the Header and Footer, visit the University Relations [Our Brand site](https://www.ur.umn.edu/brand/) to download an alternate version.

### JavaScript
The JavaScript for implementing the dropdown header is included in the file `js/umnhf-2015.js`. This was written to not be dependent on any libraries.

### Styles
Styles are designed for maximum (excessive?) specificity, unlikely to override or be overridden. In any case in which you do need to override the CSS for some reason (tread carefully).