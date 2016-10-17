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
IMPORTANT : Checkout a branch from jvevents 
 Actually there is only events-dev: (in the future it will be 'events-stable' or "events-someNewFeature" )
 
 git fetch
 git checkout events-dev
 
 if you want to push changes, you need to get write Access to the events repository or you need to create your own fork
 and then Create Pull requests  that your changes get merged
  
 

