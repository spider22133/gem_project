{"version":3,"file":"script.min.js","sources":["script.js"],"names":["BX","LiveFeedClass","parameters","this","ajaxUrl","socnetGroupId","randomString","listData","_this","addCustomEvent","iblock","init","ID","NAME","DESCRIPTION","PICTURE","CODE","window","SBPETabs","changePostFormTab","prototype","Array","iblockId","iblockName","iblockDescription","iblockPicture","iblockCode","setPicture","setTitle","getList","isConstantsTuned","ajax","method","dataType","url","addToLinkParam","data","sessid","bitrix_sessid","onsuccess","delegate","result","status","value","k","templateData","admin","setResponsible","notifyAdmin","errors","showModalWithStatusAction","message","pop","innerHTML","util","htmlspecialchars","lists","findChildrenByClassName","i","length","show","hide","appendChild","create","props","id","className","attrs","type","style","adjust","html","unbindAll","setAttribute","removeElement","elem","parentNode","removeChild","link","name","remove_url_param","indexOf","response","action","getFirstErrorFromResponse","messageBox","children","text","currentPopup","PopupWindowManager","getCurrentPopup","destroy","idTimeout","setTimeout","w","uniquePopupId","close","popupConfirm","content","onPopupClose","clearTimeout","autoHide","zIndex","onmouseover","e","onmouseout","addNewTableRow","tableID","col_count","regexp","rindex","tbl","document","getElementById","cnt","rows","oRow","insertRow","oCell","insertCell","cells","replace","arguments","parseInt","addNewFileTableRow","tmp","createElement","firstChild","lastChild","getNameInputFile","wrappers","getElementsByClassName","inputs","getElementsByTagName","j","onchange","getName","createAdditionalHtmlEditor","tableId","fieldId","formId","sHTML","p","s","n","substr","idEditor","fieldIdName","BXHtmlEditor","Show","inputName","width","height","allowPhp","limitPhpAccess","templates","templateId","templateParams","componentFilter","snippets","placeholder","actionUrl","cssIframePath","bodyClass","bodyId","spellcheck_path","usePspell","useCustomSpell","bbCode","askBeforeUnloadPage","settingsKey","showComponents","showSnippets","view","splitVertical","splitRatio","taskbarShown","taskbarWidth","lastSpecialchars","cleanEmptySpans","lazyLoad","showTaskbars","showNodeNavi","controlsMap","compact","sort","separator","wrap","autoResize","autoResizeOffset","minBodyWidth","normalBodyWidth","htmlEditor","editorId","getAttribute","frameArray","createSettingsDropdown","PreventDefault","menu","PopupMenu","getMenuById","popupWindow","isShown","settingsDropdown","offsetTop","offsetLeft","angle","offset","events","setDelegateResponsible","modalWindow","modalId","title","overlay","contentStyle","paddingTop","paddingBottom","onAfterPopupShow","popup","findChild","contentContainer","cursor","bind","proxy","_startDrag","buttons","click","selectSpan","selectUsers","push","listUser","selected","BXfpListsSelectCallback","jumpSettingProcess","location","href","jumpProcessDesigner","manyTemplate","notify","userId","siteDir","siteId","listAdmin","img","form","tag","onsubmit","el","getRealDisplay","old","display","nodeName","body","displayCache","testElem","currentStyle","getComputedStyle","computedStyle","getPropertyValue","params","bindElement","closeIcon","right","top","Math","random","withoutContentWrap","contentClassName","withoutWindowManager","contentDialogChildren","concat","hasOwnProperty","contentDialog","onPopupShow","firstButtonInModalWindow","_keyPress","proxy_context","closePopup","unbind","_keypress","windowsWithoutManager","PopupWindow","closeByEsc","isNaN","submitForm","submitBlogPostForm","addClass","submitAjax","processData","startResult","parseJSON","undefined","removeClass","errorPopup"],"mappings":"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"}