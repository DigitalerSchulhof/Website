website.elemente = {
  daten: (id, element) => {
    let r = {
      status: $("#"+id+"Status").getWert()
    };
    let felder = $("#"+id+"Felder").getWert().split(";");
    for(let i = 0; i < felder.length;) {
      r[felder[i++]] = $("#"+id+felder[i++]).getWert();
    }
    let fn = window.website.element;
    fn = fn[element];
    if (fn !== undefined) {
      fn = fn["daten"];
      if (fn !== undefined) {
        r = {...r, ...fn.call(null, id, element, r)};
      }
    };
    return r;
  },
  neu: {
    fenster:    (element, position, seite, sprache) =>  ui.fenster.laden("Website", 15, {element: element, position: position, seite: seite, sprache: sprache}),
    speichern:  (element, position, seite, sprache) =>  core.ajax("Website", 16, "Element erstellen", {element: element, position: position, seite: seite, sprache: sprache, ...website.elemente.daten("dshWebsiteElementNeu", element)})
                                                              .then(_ => ui.laden.meldung("Website", 12, null)),
  },
  bearbeiten: {
    fenster:    (element, id) =>  ui.fenster.laden("Website", 17, {element: element, id: id}),
    speichern:  (element, id) =>  core.ajax("Website", 18, "Element bearbeiten", {element: element, id: id, ...website.elemente.daten("dshWebsiteElementBearbeiten"+element+"_"+id, element)})
                                        .then(_ => ui.laden.meldung("Website", 13, null, {element: element, id: id})),
  },
  loeschen: {
    fragen:     (element, id) =>  ui.laden.meldung("Website", 16, "Element löschen", {element: element, id: id}),
    ausfuehren: (element, id) =>  core.ajax("Website", 20, null, {element: element, id: id})
                                        .then(_ => ui.laden.meldung("Website", 17, null, { element: element, id: id }))
  }
};