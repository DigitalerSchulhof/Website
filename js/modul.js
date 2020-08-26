website.modul = {
  einstellungen: {
    sprachen: () => {
      var standardsprache    = $("#dshModulWebsiteStandardsprache").getWert();
      core.ajax("Website", 0, "Sprachen ändern", {standardsprache: standardsprache}, 0);
    }
  }
};