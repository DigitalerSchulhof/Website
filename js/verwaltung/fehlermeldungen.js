website.verwaltung.fehlermeldungen = {
  daten: id => ({
    titel403:   $("#"+id+"Titel403").getWert(),
    inhalt403:  $("#"+id+"Inhalt403").getWert(),
    titel404:   $("#"+id+"Titel404").getWert(),
    inhalt404:  $("#"+id+"Inhalt404").getWert(),
  }),
  speichern: sprache => core.ajax("Website", 21, "Änderungen speichern", {sprache: sprache, ...website.verwaltung.fehlermeldungen.daten("dshVerwaltungFehlermeldung")}, 18),
  sprache: _ => {
    core.seiteLaden($("#dshVerwaltungFehlermeldungenSprachwahl").getWert());
    let spracheFokus = _ => {
      $("#dshVerwaltungFehlermeldungenSprachwahl")[0].focus();
      window.removeEventListener("dshSeiteGeladen", spracheFokus);
    };
    window.addEventListener("dshSeiteGeladen", spracheFokus);
  }
};