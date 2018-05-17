# joomla-rotate-plugin

# Description
Joomla 3+ plugin for displaying one type of content for registered users and another type of content for guests within an article.

# Usage
Content can be displayed inline or using a module:

Using Inline content
Guests will see the content within these tags {guest}{/guest}.

**Example:**
{guest}content to display{/guest}
Will show for guests: content to display

and Registered users will see the content within these tags {registered}{/registered}.

**Example:**
{registered}content to display{/registered}
Will show for registered users: content to display

## Using in Modules
To include content for Guests, use this:
{displayguest modulename}  

If a user is not logged in, they will see the module called "modulename"  

To include content for Registered users, use this:  
{displayreg modulename}  

Registered users will see "modulename"  
  
Where "modulename" is the custom module.  

# Contribute
The repository is mainly if you want to change the code and modify the plugin to fit your needs. If you just want to download and run it on Joomla, you can do it from here:  
https://www.alttechnical.com/rotate-plugin-for-joomla

# Reporting bugs
Report bugs to us directly:  
https://www.alttechnical.com/contact-form
