# Actions

Actions are PHP Code (Class of extending Action), which is sort of like a
Workflow. Actions can be attached to many different items or events in Centro.

Examples of their usefulness could be:
* Count how many times a UrlRoute has matched
* Log which UrlRoutes And Parameters Match most frequently
  * *(Possibly good for automatic optimizations)?* Reprioritize the UrlRoutes list, with most common floating to the top.
  
**Actions cannot produce output to the browser.**
