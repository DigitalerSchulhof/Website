website.elemente = {
  daten: id => {
    let r = {
      status: $("#"+id+"Status").getWert()
    };
    let felder = $("#"+id+"Felder").getWert().split(";");
    for(let i = 0; i < felder.length;) {
      r[felder[i++]] = $("#"+id+felder[i++]).getWert();
    }
    return r;
  },
  bearbeiten: {
    fenster:    (element, id, sprache) => ui.fenster.laden("Website", 17, null, {element: element, id: id, sprache: sprache}),
    speichern:  (element, id, sprache) => core.ajax("Website", 18, "Element bearbeiten", {element: element, id: id, sprache: sprache, ...website.elemente.daten("dshWebsiteElementBearbeiten"+element+"_"+id)})
                                                .then(() => ui.laden.meldung("Website", 12, null, {element: element, id: id})),
  }
};