website.verwaltung.seiten = {
  suchen: sort => {
    let sprache = $("#dshVerwaltungSeitenSprachwahl").getWert();
    return core.ajax("Website", 7, null, {sprache: sprache, ...sort});
  },
  daten: (id) => {
    let r = {
      art:            $("#"+id+"Art").getWert(),
      status:         $("#"+id+"Status").getWert(),
    };
    r.sprachen = {};
    let sprachen = $("#"+id+"Sprachen").getWert().split(";");
    for(let s of sprachen) {
      r.sprachen[s] = {
        bezeichnung:  $("#"+id+"Bezeichnung"+s).getWert(),
        pfad:         $("#"+id+"Pfad"+s).getWert(),
      };
    }
    return r;
  },
  neu: {
    // »id« ist hier die übergeordnete Seite um den Bezug nicht zu verlieren
    fenster:    id => ui.fenster.laden("Website", 8, {id: (id || null)}),
    speichern:  id => core.ajax("Website", 9, "Seite anlegen", {id: (id || null), ...website.verwaltung.seiten.daten("dshVerwaltungSeiteNeu")}, 6, "dshVerwaltungSeiten")
  },
  bearbeiten: {
    fenster:    id => ui.fenster.laden("Website", 10, {id: id}),
    speichern:  id => core.ajax("Website", 11, "Seite bearbeiten", {id: id, ...website.verwaltung.seiten.daten("dshVerwaltungSeiteBearbeiten"+id)}, null, "dshVerwaltungSeiten")
                            .then(_ => ui.laden.meldung("Website", 7, null, {id: id})),
  },
  loeschen: {
    fragen:     id => ui.laden.meldung("Website", 8, "Seite löschen", {id: id}),
    ausfuehren: id => core.ajax("Website", 12, null, {id: id}, 9, "dshVerwaltungSeiten")
  },
  startseite: {
    fragen:     id => ui.laden.meldung("Website", 10, "Zur Startseite machen", {id: id}),
    ausfuehren: id => core.ajax("Website", 13, null, {id: id}, 11, "dshVerwaltungSeiten")
  },
  setzen: {
    status:     (id, status)  => core.ajax("Website", 14, "Status ändern", {id: id, status: status}, null, "dshVerwaltungSeiten")
                                       .then(_ => ui.laden.aus()),
    version: {
      fragen:     (id, version, sprache) => ui.laden.meldung("Website", 14, "Daten ändern", {id: id, version: version, sprache: sprache}),
      ausfuehren: (id, version, sprache) => core.ajax("Website", 19, null, {id: id, version: version, sprache: sprache})
                                                  .then(_ => ui.laden.meldung("Website", 15, null, {version: version}))
    }
  }
};