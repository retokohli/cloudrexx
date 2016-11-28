<?php

// When using Translateable:
// Suppose you just did
//  $query->getResult();
// Now, Doctrine must be forced to clear cached Entries,
// or the previous results will be returned again!
// Note that this may impact performance,
// as *all* Doctrine caches are flushed.
// Note:
//  $entryRepo->clear(); // Exception: Not implemented
// and
//  $class = $em->getClassMetadata('Cx\\Modules\\Topics\\Model\\Entity\\Entry');
//  $em->clear($class->rootEntityName); // Exception: Not implemented
// Thus,
//    $em->clear();
//    $this->cx->getDb()->getTranslationListener()
//        ->setTranslatableLocale($parameters->getLocaleDetail());
//    $query = $entryRepo->createQueryBuilder('entry')
//        ->orderBy('entry.id', 'ASC')
//        ->getQuery();
// Now you get the translated entities
//    $entries_detail = $query->getResult();

// TEST: See which constants and InitCMS language properties are defined in API mode:
// Called in ComponentController::executeCommand()
/** @var \InitCMS */
global $objInit;
\DBG::log("FrontendController::parseGlobals(): DEBUG:"
    . " LANG_ID: " . LANG_ID // Use of undefined constant
    . ", FRONTEND_LANG_ID: " . FRONTEND_LANG_ID // Use of undefined constant
    . ", BACKEND_LANG_ID: " . BACKEND_LANG_ID // Use of undefined constant
    . ", backendLangId: " . $objInit->backendLangId // empty (tested in frontend)
    . ", frontendLangId: " . $objInit->frontendLangId // empty (tested in frontend)
    . ", frontendLangName: " . $objInit->frontendLangName // empty (tested in frontend)
    . ", userFrontendLangId: " . $objInit->userFrontendLangId // empty (tested in frontend)
    . ", defaultBackendLangId: " . $objInit->defaultBackendLangId // OK
    . ", defaultFrontendLangId: " . $objInit->defaultFrontendLangId // OK
    . ", arrLang: " . var_export($objInit->arrLang, true) // OK
    . ", arrLangNames: " . var_export($objInit->arrLangNames, true) // OK
);

// Sorting by name (or other object property):
// Note: The Collator class works just as well, but is much slower
// than iconv().
//$collator = new \Collator($parameters->getLocaleList());
usort($entries, function($a, $b) { //use ($collator) {
    //return $collator->compare($a->getName(), $b->getName());
    return strcmp(iconv('UTF-8', 'ASCII//IGNORE', $a->getName()),
        iconv('UTF-8', 'ASCII//IGNORE', $b->getName()));
});
