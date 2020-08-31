website.verwaltung.sprachen = {
  suchen: sort => core.ajax("Website", 0, null, {...sort}),
  daten: id => ({
    a2:           $("#"+id+"A2").getWert(),
    name:         $("#"+id+"Name").getWert(),
    namestandard: $("#"+id+"NameStandard").getWert(),
    alt:          $("#"+id+"Alt").getWert(),
    aktuell:      $("#"+id+"Aktuell").getWert(),
    neu:          $("#"+id+"Neu").getWert(),
    sehen:        $("#"+id+"Sehen").getWert(),
    bearbeiten:   $("#"+id+"Bearbeiten").getWert(),
    fehler:       $("#"+id+"Fehler").getWert(),
  }),
  neu: {
    fenster:    _ => ui.fenster.laden("Website", 1),
    speichern:  _ => core.ajax("Website", 2, "Sprache anlegen", {...website.verwaltung.sprachen.daten("dshVerwaltungSpracheNeu")}, 0, "dshVerwaltungSprachen")
  },
  bearbeiten: {
    fenster:    id => ui.fenster.laden("Website", 3, {id: id}),
    speichern:  id => core.ajax("Website", 4, "Sprache bearbeiten", {id: id, ...website.verwaltung.sprachen.daten("dshVerwaltungSpracheBearbeiten"+id)}, null, "dshVerwaltungSprachen")
                            .then(_ => ui.laden.meldung("Website", 1, null, {id: id})),
  },
  loeschen: {
    fragen:     id => ui.laden.meldung("Website", 2, "Sprache löschen", {id: id}),
    ausfuehren: id => core.ajax("Website", 5, null, {id: id}, 3, "dshVerwaltungSprachen")
  },
  standardsprache: {
    fragen:     id => ui.laden.meldung("Website", 4, "Standardsprache festlegen", {id: id}),
    ausfuehren: id => core.ajax("Website", 6, null, {id: id}, 5, "dshVerwaltungSprachen")
  }
};