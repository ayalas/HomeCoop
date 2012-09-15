#create-language-folders.py allows website application authors to generate separate folders for each language their website is designed to support.
#Copyright (C) 2012  Ayala Shani ayalashah@joindiaspora.com

#This program is free software: you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation, either version 3 of the License, or
#(at your option) any later version.

#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.

#You should have received a copy of the GNU General Public License
#along with this program.  If not, see <http://www.gnu.org/licenses/>.

import sys
import os
import string
import os.path
import fnmatch
import xml.dom.minidom
import shutil
import html

#------------------------------------------------ usage  ----------------------------------------------------------------------------

# python[3.2+] [create-languages-folders.py path] [target path with a $keys sub-folder]

# example:
# python3.2 /home/user/workspace/MyWebSite/create-language-folders.py /home/user/workspace/MyWebSite

# note: supported-languages.xml and strings file[s] in the name format strings.[language folder].xml (+ strings.xml for default/non-language specific placeholders) must be in the same path as create-languages-folders.py

#--------------------------------------------------------- easily-modifyable constants  -------------------------------------

# the file extensions to distinguise while processing the $keys folder
glExtensions = [ 'htm', 'js', 'php', 'css', 'xsl' ]
# whether files having the above extensions contain placeholders that should be processed (=True) or just copied as is to the language-specific folder (=False)
gbProcessMatchingFiles = True
# placeholder starting mark
gsPHStart = '<!$'
# placeholder ending mark
gsPHEnd = '$!>'

# the file extensions to set the html dir element in, if the langauge requires (such as in Arabic and Hebrew)
glDirElementExtensions = [ 'htm','php' ]
# HTML tag to replace. Only html tags written exactly like this, will be replaced by <html dir='rtl'> when required (in Arabic and Hebrew, for instance)
gsHtmlTagForDirElement = '<html>'
# HTML tag count to replace by <html dir='rtl'> when required (in Arabic and Hebrew, for instance). 1 means only the first <html> tag will be replaced
gnHtmlTagCountForDirElement = 1

#---------------------------------------------------------------------------------------------------------------------------------------

# this function returns whether a file is set for place-holder processing, according to whether its extension matchs an extension from glExtensions
# and what behaviour was chosen for matching files (processing them or not?) in gbProcessMatchingFiles 
def IsFileToProcess( sFileName ):
    global glExtensions
    global gbProcessMatchingFiles
    for ext in glExtensions:
        if fnmatch.fnmatch(sFileName, '*.' + ext):
            return gbProcessMatchingFiles
    return not gbProcessMatchingFiles

def IsFileToSetDir( sFileName ):
    global glDirElementExtensions
    for ext in glDirElementExtensions:
        if fnmatch.fnmatch(sFileName, '*.' + ext):
            return True
    return False

def GetNodeText( nodelist ):
    for node in nodelist:
        if node.hasChildNodes() and node.childNodes[0].nodeType == xml.dom.minidom.Node.TEXT_NODE:
            return node.childNodes[0].nodeValue
    return ''

def GetFirstElementValue( node, elm ):
    nodelist = node.getElementsByTagName( elm )
    return GetNodeText( nodelist )

def CopyFile(sSourceFilePath):
    print( "copying file {} ...".format( sSourceFilePath ))
    for l in iter( gdctLangs ):
        tmp = string.Template( sSourceFilePath )
        shutil.copy2(sSourceFilePath, tmp.substitute(keys = l))

def ProcessFile(sSourceFilePath, bForDirSet ):
    global gsPHStart
    global gsPHEnd
    global gnStartPHLen    
    global gnEndPHLen   
    nPos = 0
    nPrev = 0
    nEnd = 0
    nLen = 0
    sKey = ''
    print( "processing file {} ...".format( sSourceFilePath ))
    with open(sSourceFilePath, 'rt', encoding="utf-8") as fSource:
        sContent = fSource.read()
    dctDestContent = gdctLangs.fromkeys(gdctLangs.keys(),'')
    nLen = len(sContent)
    while nPos != -1:
        nPos = sContent.find( gsPHStart, nPos )
        if nPos >= 0:
            for l in iter( gdctLangs ):
                dctDestContent[l] = dctDestContent[l] +  sContent[ nPrev: nPos ] # copy interval between placeholders/from start of file
            nPrev = nPos
            nPos = nPos + gnStartPHLen
            nEnd = sContent.find( gsPHEnd, nPos )
            if nEnd >= 0:
                # we got a key to search in the dictionary
                sKey = sContent[ nPos: nEnd ]
                nPrev = nEnd + gnEndPHLen #skip the key
                for l in iter(gdctLangs):
                    if sKey in iter(gdctLangs[l][1]):
                        dctDestContent[l] = dctDestContent[l] + gdctLangs[l][1][sKey] # copy the translation
                    elif len(gdctLangs[l][2]) > 0 and sKey in iter(gdctLangs[ gdctLangs[l][2] ][1]): #check falling language
                        dctDestContent[l] = dctDestContent[l] + gdctLangs[ gdctLangs[l][2] ][1][sKey] # copy the falling language translation
                    elif sKey in iter(gdctDefaults):
                        dctDestContent[l] = dctDestContent[l] + gdctDefaults[sKey] # get default value from strings.xml
    # write to all language folders
    for l in iter( gdctLangs ):
        dctDestContent[l] = dctDestContent[l] +  sContent[ nPrev: ] # copy until end of file
        # replace first html tag int the file
        if bForDirSet and len(gdctLangs[l][0]) > 0:
            dctDestContent[l] = dctDestContent[l].replace( gsHtmlTagForDirElement, "<html dir='{}' >".format( gdctLangs[l][0]),  gnHtmlTagCountForDirElement )
        tmp = string.Template( sSourceFilePath )
        with open ( tmp.substitute(keys = l), 'wt', encoding="utf-8") as fDest:
            fDest.write( dctDestContent[l] )

def ProcessDir( sDirPath ):
    global gsTargetRootDir
    # first create root dirs for each language
    for l in iter(gdctLangs):
        sNewLangDir  = gsTargetRootDir + '/' + l
        if not os.path.isdir( sNewLangDir ):
            os.mkdir( sNewLangDir )
    # loop through the $keys folder
    for root, dirs, files in os.walk( sDirPath ):
        # create all dirs for each language
        for dr in dirs:
            tmp = string.Template( root + '/' + dr )
            for lang in iter(gdctLangs):
                sLDir = tmp.substitute( keys=lang)
                if not os.path.isdir( sLDir ):
                    os.mkdir( sLDir )
        # copy all files for each language
        for fn in files:
            sFullSourceFilePath = root + '/' + fn
            # replace strings only in some text files, as defined by script parameters, for each language
            if IsFileToProcess( fn ):               
                ProcessFile ( sFullSourceFilePath, IsFileToSetDir( fn ) ) #process place holders
            else:
                CopyFile ( sFullSourceFilePath ) #copy other files (binaries, etc.)

def LoadLanguages( ):
    global gdctLangs
    global gsFoldersMsg
    #get default values
    docDefaults = xml.dom.minidom.parse( 'strings.xml' )
    colStrings = docDefaults.documentElement.getElementsByTagName("str")
    for s in colStrings:
        gdctDefaults[GetFirstElementValue( s, "key" )] = GetFirstElementValue( s, "value" )
    docDefaults.unlink()
    docLangs = xml.dom.minidom.parse( 'supported-languages.xml' )
    colLangs = docLangs.documentElement.getElementsByTagName("language")
    sFolder = ''
    for l in colLangs:
        sDirElement = ''
        sFallingLang = ''
        sFolder = l.attributes[ "key" ].value
        if l.attributes.getNamedItem( "dir" ) != None:
            sDirElement = l.attributes[ "dir" ].value
        if l.attributes.getNamedItem( "falling-language" ) != None:
            sFallingLang = l.attributes[ "falling-language" ].value        
        if len(gsFoldersMsg) == 0:
            gsFoldersMsg = sFolder
        else:
            gsFoldersMsg = gsFoldersMsg + ', ' + sFolder
        docLang = xml.dom.minidom.parse( 'strings.' + sFolder + '.xml' )
        colStrings = docLang.documentElement.getElementsByTagName("str")
        d = dict()
        for s in colStrings:
            d[GetFirstElementValue( s, "key" )] = html.escape(GetFirstElementValue( s, "value" ), True)
        gdctLangs[sFolder] = (sDirElement, d, sFallingLang)
        docLang.unlink()
    docLangs.unlink()

gsScriptRootDir = '.'
if len(sys.argv) >= 1:
    gsScriptRootDir = os.path.split( sys.argv[0] )[0]
    if gsScriptRootDir == '':
        gsScriptRootDir = '.'
    elif gsScriptRootDir != '.':
        os.chdir( gsScriptRootDir )

gsTargetRootDir = '.'
if len(sys.argv) >= 2:
    gsTargetRootDir = sys.argv[1]
    if gsTargetRootDir == '':
        gsTargetRootDir = '.'

if not os.path.isdir(gsTargetRootDir) or not os.path.isdir( gsTargetRootDir + '/$keys')or not os.path.isfile(  'supported-languages.xml' ):
    print ('\nusage:\n\n    python[3.2+] [create-languages-folders.py path] [target path with a $keys sub-folder]\n\nnote: supported-languages.xml and strings file[s] in the name format strings.[language folder].xml must be in the same path as create-languages-folders.py' )
    exit()

gdctLangs = dict()
gdctDefaults = dict()
gsFoldersMsg = ''
gnStartPHLen = len( gsPHStart )
gnEndPHLen = len( gsPHEnd )

LoadLanguages( )

ProcessDir( gsTargetRootDir + '/$keys')

del gdctLangs

print('\ndone creating the following language folders: ' + gsFoldersMsg + '\n')
