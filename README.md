# jv_events
#TYPO3 Extension jv_events - 

Manage Events, Seminars, Classes and registrations

See the Documentation Folder in this extension and in the Future the Wiki pages

Adding jv_events to your TYPO3 Project:

##3 ways:
a) in the future you can download it as usual from TYPO3 TER
Install it and include the STATIC Extension Template

b) clone this repository or download it and copy directory to your TYPO3 project

c) If you are using GIT also for your project, you may want to include it as a  Submodule:
see infos here:  https://git-scm.com/book/de/v1/Git-Tools-Submodule

go to your VCS root. In our example the Website Root is "./http" and the git repository root is "."
f.e 
git Root: C:/projects/vagrant/allplan/
website Root Root: C:/projects/vagrant/allplan/http

cd C:/projects/vagrant/allplan/http
git submodule add git@github.com:velletti/jv_events.git http/typo3conf/ext/jv_events


### on other mashine z.B.: webserver , second developer ):
  git submodule update --init --recursive
  
###In PHP Storm:
Settings -> Version Control -> add a new Repository  
C:/projects/vagrant/allplan/http/typo3conf/ext/jv_events

Then you can IDE Controls for each repository separate

###IMPORTANT : Checkout a branch from jvevents 
 Actually use TYPO3-9-10-LTS 
 
 git fetch
 git checkout TYPO3-9-10-LTS
 
 if you want to push changes, you need to get write Access to the events repository or you need to create your own fork
 and then Create Pull requests  that your changes get merged
 
 
  
 To keep Updated on your server you need to switch to the jv_events Folder and initiate a 
 git pull 
 or in the outside repository:
 
 git submodule update --recursive
 
 ### backend Modul in LTS9 - 
 add this to your typocript ! 
 
     
     module.tx_jvevents_eventmngt {
     	view {
     		# cat=module.tx_jvevents_eventmngt/file; type=string; label=Path to template root (BE)
     		templateRootPath = EXT:jv_events/Resources/Private/Backend/Templates/
     		# cat=module.tx_jvevents_eventmngt/file; type=string; label=Path to template partials (BE)
     		partialRootPath = EXT:jv_events/Resources/Private/Backend/Partials/
     		# cat=module.tx_jvevents_eventmngt/file; type=string; label=Path to template layouts (BE)
     		layoutRootPath = EXT:jv_events/Resources/Private/Backend/Layouts/
     	}
     	persistence {
     		# cat=module.tx_jvevents_eventmngt//a; type=string; label=Default storage PID
     		storagePid =
     	}
     }
 

 

 ### QR Codes .. if you want to use QR Codes:
 
      composer require endroid/qr-code
      
      

## Upgrade to TYPO3 LTS

### switchable Controllers have been removed. 
This requires Update of all tt_content elements -> pi_flexform -> field switchableControllerActions
there is an upgrade Wizard  that hopefully will  do  this for you. 
Will not work, if plugin was used, but the ReplaceString does not fit to latest V11  version of jvevents.


### typoscript requires update

needs to add at least this lines to your local typoscript:

    plugin.tx_jvevents_event < plugin.tx_jvevents_events
    plugin.tx_jvevents_location < plugin.tx_jvevents_events
    plugin.tx_jvevents_locations < plugin.tx_jvevents_events
    plugin.tx_jvevents_registrant < plugin.tx_jvevents_events
    plugin.tx_jvevents_organizer < plugin.tx_jvevents_events
    plugin.tx_jvevents_organizers < plugin.tx_jvevents_events
    plugin.tx_jvevents_curl < plugin.tx_jvevents_events
    plugin.tx_jvevents_ajax < plugin.tx_jvevents_events

include jv-events.css in correct way : EXR: and not: typo3conf/ext/ ... 


 

