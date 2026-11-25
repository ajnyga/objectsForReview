# Objects for Review Plugin

OJS 3.5

Manage reviews of books and other publications in OJS. The plugin links each review article to the publication it reviews (the "object for review") and lets journals offer publications for review on their website. It continues the idea of the Objects for Review and Books for Review plugins for OJS 2.x.

## What it does

- **Offer publications for review.** Journal managers list available publications in *Settings → Website → Objects for review*. A public page (`/for-review`) lists them, and can be added to the site menu with the *Objects for Review* navigation menu item.
- **Reservations.** Logged-in users can reserve a publication on the public page, or cancel their reservation. The editor gets an email for each reservation and cancellation.
- **Linking the review to the publication.** When submitting their review, authors attach the publication they reserved. They can also enter the details of a publication themselves, unless the journal only allows publications listed by the managers. Editors can add and edit these details in the workflow under *Publication → Objects for Review*.
- **Display on the article page.** Optionally, the reviewed publications are listed on the article's landing page, with a link if the identifier is a URL.

Each object records the authors, title, publisher, year, an identifier (DOI, ISBN, URL and others) and a COAR resource type.

## Settings

- **Only allow objects created by the manager:** authors can only attach publications they have reserved.
- **Display a list of reviewed objects under the abstract:** show the list on article pages.
- **Notification email:** where reservation emails are sent (defaults to the journal's primary contact).
- **Public instructions:** text shown at the top of the public page.


## Development

The editorial screens (workflow, submission wizard and the website settings tab) are Vue components in `resources/js`. The built files in `public/build` and the translation key list in `registry/uiLocaleKeysBackend.json` are committed, so installing the plugin does not need Node.js.

To change the components, rebuild with Node.js 20.19+, 22.13+ or 24+:

```
npm install
npm run build   # or: npm run dev (rebuild on every change)
```

The components use the plugin's REST API at `api/v1/objectsForReview`.

## License

This plugin is licensed under the GNU General Public License v3. See the file LICENSE for the complete terms of this license.

[![Plugin created by The Federation of Finnish Learned Societies](https://img.shields.io/badge/Plugin%20created%20by-The%20Federation%20of%20Finnish%20Learned%20Societies-5A2A82?style=for-the-badge&labelColor=4B196E&logoColor=white)](https://tsv.fi/en/)