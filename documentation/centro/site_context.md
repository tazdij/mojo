# Centro: SiteContext

A site_context is at a minimum, a record in the contexts table
of the default database, which all entities and other data
is able to reference, as the context in which it belongs. **Contexts** are
intended to allow multiple domains to be hosted from the same Centro install.

## The pitch
Imagine if you will, you own a resort & camping property, and you would like
each to have their own marketing materials, and each to have a distinct brand
which only ever-so-often cross paths. Usually only in the form
of intentional cross promotion. With many CMS installs, you would be left to
require two installs, one for each domain name. But here, we would like to ease
the management of your online properties. You can log into your Centro Admin
and then manage your multiple sites.

With the SiteContext system, you can even share Assets (Downloads, Images, 
Styles, Javascript, and other static media) between context, with automatic
linking and reference tracking. Allowing you to search your uploaded Assets when
editing/publishing content. When used or uploaded, the SiteContext will be set
accordingly. (It should be possible to disable cross SiteContext sharing)

Another possible benefit to this multi-site SiteContext system is; allowing
employees/users/admins/etc to have access to manage/edit/contribute/etc to one
or more SiteContexts. This can apply to visitors as well. You might run a
community with multiple verticals, a Fitness and Wellness brand for example.
You could have a Membership site dedicated to your content and training materials
of which members have access. You could offer either as a free bonus or paid upgrade;
access to a second SiteContext, which is your Recipe database/website. Even adding
customer features for members to create meal plans, manage their recipes  and generate
shopping lists for their meal plans. All of this from a single install, and simply
multipls SiteContexts.

## How SiteContext is implemented.

At the moment, all Entities, Assets, etc all have a context_id field, which
points to a SiteContext, configured in the Admin. During the Centro install,
your first context is configured. You can add/remove additional SiteContexts and map
them to URLs.

### Routing Notes

Even though you can configure your domain name to SiteContexts in the Admin,
you will still need to make sure your HTTPd application is routing the requests to
your Centro installation. If you have a server to yourself (VPS, Cloud, or Dedicated)
you can make it route all requests to your Centro install.