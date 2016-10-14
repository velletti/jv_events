.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Target group: **Administrators**


.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Go to the Extension Manager
#. Install the extension
#. Load the static template
#. Copy needed Templates/Partials to your own Template Folder and adapt them...


Some Configuration
  This option enables...



.. figure:: ../Images/AdministratorManual/ExtensionManager.png
   :alt: Extension Manager

   Extension Manager (caption of the image)

   List of extensions within the Extension Manager also shorten with "EM" (legend of the image)


.. _admin-configuration:

Configuration
-------------

* Where and how the extension should be configured? TypoScript? PHP?

* Are there other prerequisite to full fill beforehand?
  For example, configure a setting in a special way somewhere.


.. _admin-faq:

FAQ
---

Possible subsection: FAQ

Subsection
^^^^^^^^^^

This Extension has a partial called "MicroFormat"
See Tester
https://search.google.com/structured-data/testing-tool/u/0/?hl=de

this is Called from Single Event View and List View and Create a JSON-LD Objekt like this

 {
	  "@context" : "http://schema.org",
	  "@type" : "Event",
	  "name" : "Band in Berlin",
      "image" : "http://www.example.com/image.jpg",
	  "startDate" : "2016-04-20T20:00",
      "endDate" : "2016-04-20T22:00",
	  "url" : "http://www.example.com/events/band/2016-04-20-2000",
      "performer" : "Name of Organizer",
      "description" : "beschreibung",
	  "offers" : {
	    "@type": "AggregateOffer",
	     "url" : "http://www.example.com/events/band/2016-04-20-2000/tickets",
	    "lowPrice" : "100",
	    "offerCount" : "1839"
	  },
	  "location" :
	  {
	    "@type" : "Place",
	    "sameAs" : "http://www.veranstaltungsort-berlin.de/",
	    "name" : "Veranstaltungsort",
	    "address" :
	    {
      "@type" : "PostalAddress",
	      "streetAddress" : "Beispielstaße 1",
	      "addressLocality" : "Berlin",
	      "postalCode" : "10243"
	    }
	  }
}

Sub-subsection
""""""""""""""

Deeper into the structure...
