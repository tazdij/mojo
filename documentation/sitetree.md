# SiteTree

The SiteTree determines what to load when a visitor is browsing the website.
It starts with a NULL root with any page at the first level being allowed to
take the position of the Index node.

## Nodes

The SiteTree is comprised of Nodes. These nodes have a Parent Node and zero or
more Child Nodes. Each Node has a Class which is an actual php Class in Centro.
These are sort of like controllers but actually run before the controller for.

Nodes can be a variety of types (all of which are just a decendent of the Node
Class). To start with there needs to be:
* SitemapNode
* HtmlNode
* MarkdownNode
* TemplateNode (TemplateLite)
* WYSIWYGNode - Not exactly HTML. It is Structured JSON, generating HTML
* EntityNode (Loads an Entity into a template)
* EntityCollectionNode (Like a WP ArchivePage, but with a Selection Query)
* DynamicEntityNode (Using a URL Parameter, can load a node of a specific type)
  * i.e. domain.com/product/1221 (where 1221 is placed into the selection query)
  * This will probably translate into a UrlRoute, and will not be included in the database directly as a FlatUrl
* DynamicEntityCollectionNode (Same as DynamicEntityNode but selects multiple)


## Nesting

The SiteTree is a nesting structure where all nodes except the Root node have a
parent. All nodes have the potential to have child nodes, however some node classes
might disable this, as it may not make any sense.

## Implementation Ideas

**Database Flat Map** - This idea is to find a way to place the SiteTree into a
table where there is no recursion. On the FrontEnd this is extremely important,
avoiding multiple selects from the database, or iteration of a nested array.
Here, it could be a simple SQL statement to try and get the correct node.

It is possible that having UrlRoutes tested first and then Nodes, is faster.
UrlRoutes will be stored as a PHP Array in a local config file.

- **FlatMap Storage** will be generated from the sitetree and cached into a PHP file in the app/cache/ directory
- **Cache Options** it is possible that the context or installation will choose a caching method. (ie `Redis`, `PHP`, `SerializedArray`, or possibly `JSON`)
