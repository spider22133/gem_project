{"version":3,"file":"script.min.js","sources":["script.js"],"names":["window","repo","fileObj","uri","BX","type","isNotEmptyString","this","id","url","name","substr","lastIndexOf","indexOf","ext","toLowerCase","ii","hasOwnProperty","diskController","manager","params","location","protocol","host","urlUpload","values","propertyName","handleBase64","delegate","catchUF","parseUF","prepareToSaveUF","prototype","getErrorText","text","message","prefixHTMLNode","userTypeId","result","filesToPost","addCustomEvent","proxy","uploadBase64","options","FileUploadOptions","ft","FileTransfer","good","response","parseJSON","bad","uploadBase64Response","uploadBase64Failure","fileKey","fileName","sessid","bitrix_sessid","mimeType","chunkedMode","upload","app","BasicAuth","success","auth_data","sessid_md5","failure","onCustomEvent","status","isArray","length","data","iconUrl","util","in_array","extension","previewImageUrl","fileId","xmlID","fieldName","fieldValue","UF","files","extraData","getId","tempId","Math","random","node","getAttribute","undefined","push","file","parseInt","replace","attachments","queue","f","add","pop","f0","fileD","removeCustomEvent","f1","uploadQueue","controller","__queueId","__onUploadOk","start","__onUploadError","error","files1","clear","q","res","hasSmthToUpload","simpleForm","handler","getRandomString","placeholder","button_name","mentionDataSource","outsection","plusAction","BXMobileApp","UI","ActionSheet","buttons","title","callback","takePhoto","source","correctOrientation","targetWidth","targetHeight","handleAppFile","show","useImageButton","action","handleAppData","handleAppCallback","repeat","comment","stopCheckWriting","__this","Page","TextPanel","getText","txt","e","writingParams","lastEvent","startCheckWriting","init","isString","setText","focus","lastFired","frequency","~text","time","Date","showWait","showLoading","closeWait","extendedForm","formSettings","attachButton","items","initFiles","attachFileSettings","resize","sendLocalFileMethod","saveToPhotoAlbum","attachedFiles","mentionButton","dataSource","return_full_mode","okname","cancelname","multiple","alphabet_index","smileButton","okButton","applyExtendedForm","cancelButton","cancelExtendedForm","controllers","cid","button","searchField","showtitle","modal","exec","MPF","enableInVersion","errors","destroy","form","hide","document","body","appendChild","elements","create","props","value","block","className","currentForm","initEvents","initControllers","error00","error01","submitPlain","submitBase64","writing","submitExtended","bound","remove","setForm","extended","edit","base64","submit","cancel","jj","getForm","ajax","prepareForm","createInstance","getInstance"],"mappings":"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"}