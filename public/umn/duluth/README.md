#UofM Header & Footer—HTML Only

Included are the files for the traditional version of the UofM header and footer and the alternate version. 

Each template file is appropriately linked to its associated CSS file and images.

## Stylesheets for Search Options

The header and footer offers two options for search behavior on small screens. 

The dropdown option shows a "Search" button that, when clicked, displays a search box below the campus wordmark.

The two-tiered version omits the "Search" button and instead drops the search below the wordmark without first hiding it or requiring a click to display.

Use one of the following stylesheets based on which version you would like to use. 

### Alternate Version
	Dropdown Search
	2015-d-alt.css

	Two-Tiered Search
	2015-d-alt-tt.css

### Traditional Version
	Dropdown Search
	2015-d-t-tt.css

	Two-Tiered Search
	2015-d-t-tt.css
	
## Internet Explorer Stylesheets
Two IE8 stylesheets are provided as well—one for the traditional and one for the alternate version. 

They are appropriately linked in their associated template file. 

## Campus Links

The campus links in the header and footer (both alternate and traditional) are optional. If you do not wish to use them you may remove them from your template. They are commented as:

> <!-- Optional campus links -->

If you choose to remove them make sure you remove them from the header and the footer. They are in a hidden `<nav>` element at full width in the footer. 

## Footer
By default each template file uses Duluth's standard footer which includes links to

- Contact Us
- Directories
- Maps and Direction
- Parking and Transportation

It also includes the mandatory EEO statement, privacy statement, and copyright information.

The copyright year will update automatically.

### Simplified Footer
If you wish to try the simplified footer you may replace that section with the HTML contained in the "simpleFooter" directory. There is also an associated CSS file to use.

### Campus Links

On small screens the campus links will drop from the header of the traditional version to the simplified footer. 

### Last Updated Date

The "Current as of..." text in the footer is optional and is commented as such. You may remove this line if you do not wish to use it.

## JavaScript
A minified version of the umnhf-2015.js file is available in the js directory.

## Responsive Breakpoints


	@media screen and (max-width: 1152px) {
	}

	@media screen and (min-width: 776px) {
	}

	@media (max-width: 775px) {
	}

	@media screen and (max-width: 750px) {
	}

	@media screen and (min-width:660px) and (max-width:979px) {
	}

	@media screen and (max-width: 674px) {
	}

	@media screen and (max-width: 659px) {
	}

	@media all and (max-width:609px) {
	}

	@media all and (max-width: 380px) {
	}
	
	@media print {
	}