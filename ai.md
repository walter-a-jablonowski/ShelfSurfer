
- groups.yml was renamed data/default_user/places.yml

 --

I have added a static button in the tab bar@view.php#L56-62 When this is pressed can you open a new tab with a DIN A4 page in landscape and multiple columns. In the columns add a printable text version of all of the tabs that have content (sections with grociery entries).

 --

Make a groceries app that can be used on smartphone using BS 5.3

Assume that we will import the groceries list from an Alexa which looks like:

```
Einkaufsliste

1. Groceries 1
2. Groceries 2
3. Groceries 3
...

Freigegeben über die Amazon Alexa App
```

Assume that we sort all groceries by vendors, and sections per vendor, groups.yml:

```
vendors:
  MyVendor:
    MySection:
      - possible buying 1
      - possible buying 2

...
```

By matching the imported entries against possible buyings of the imported list you know which section the imported entry belongs to.

- Add a small navbar (make it small)
  - Logo and app name
  - Settings icon right aligned (currently unused)
- Add a ios like tab bar below (use icon and title attrib no text)
  - has entries for the first 2 vendors
  - an entry "..." add a drop-up
    - for all left vendors
  - Import icon: when pressed a modal appears with a text area where the text export from Alexa is pasted. Persist the currently imported list in a seperate yml file.

- Content area (scrollable): Use one card per section
  - Headline (bold)
  - List of imported entries of the section
    - with check and text
    - when an entry is checked the text gets more transparent
  - each card gets its own bg color
