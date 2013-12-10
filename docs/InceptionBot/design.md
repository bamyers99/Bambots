InceptionBot Design
===================

Overview
--------
InceptionBot analyzes new Wikipedia articles to see if they may be of interest to various WikiProjects and Portals.

Program flow
------------

1. Retrieve names of new pages for the past 14 days
2. Retrieve names of moved pages for the past 14 days
3. Substitute moved page names for original page names
4. Retrieve names of updated pages for the past 1 day
5. Retrieve page contents for new and updated pages for the past 1 day

For each set of rules:

1. Retrieve the existing results page
2. Skip articles already in the results
3. Process rule regexes against the new and updated pages for the past 1 day
4. For articles that meet or exceed the threshold, report them and log the regex matches
5. Update the results and log pages
