website.verwaltung.seiten = {
  suchen: sort => core.ajax("Website", 7, null, {...sort}),
  daten: (id) => ({
    bezeichnung:  $("#"+id+"Bezeichnung").getWert(),
    pfad:         $("#"+id+"Pfad").getWert(),
    status:       $("#"+id+"Status").getWert(),
  }),
  neu: {
    fenster:    () => ui.fenster.laden("Website", 8, null),
    speichern:  () => core.ajax("Website", 9, "Sprache anlegen", {...website.verwaltung.seiten.daten("dshVerwaltungSpracheNeu")}, 5, "dshVerwaltungSprachen")
  },
  bearbeiten: {
    fenster:    id => ui.fenster.laden("Website", 10, null, {id: id}),
    speichern:  id => core.ajax("Website", 11, "Sprache bearbeiten", {id: id, ...website.verwaltung.seiten.daten("dshVerwaltungSpracheBearbeiten"+id)}, null, "dshVerwaltungSprachen")
                            .then(() => ui.laden.meldung("Website", 7, null, {id: id})),
  },
  loeschen: {
    fragen:     id => ui.laden.meldung("Website", 8, "Sprache löschen", {id: id}),
    ausfuehren: id => core.ajax("Website", 12, null, {id: id}, 9, "dshVerwaltungSprachen")
  },
  standardsprache: {
    fragen:     id => ui.laden.meldung("Website", 10, "Standardsprache festlegen", {id: id}),
    ausfuehren: id => core.ajax("Website", 13, null, {id: id}, 11, "dshVerwaltungSprachen")
  }
};