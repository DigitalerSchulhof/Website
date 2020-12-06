import ajax, { AjaxAntwort, ANTWORTEN } from "ts/ajax";
import $ from "ts/eQuery";
import * as uiFenster from "module/UI/ts/elemente/fenster";
import * as uiLaden from "module/UI/ts/elemente/laden";
import elementListe from "./elemente/_export";
import { Status, Version } from "./_export";
import { neuladen } from "ts/laden";

export interface ElementDaten {
  status: Status;
  [key: string]: any;
}

export const daten = (id: string, element: string): ElementDaten => {
  let r: ElementDaten = {
    status: $("#" + id + "Status").getWert() as Status,
  };
  const felder = $("#" + id + "Felder").getWert().split(";");
  for (let i = 0; i < felder.length;) {
    r[felder[i++]] = $("#" + id + felder[i++]).getWert();
  }
  let fn = (window as Window & typeof globalThis & { website: { element: typeof elementListe } }).website.element as any;
  fn = fn[element];
  if (fn !== undefined) {
    fn = fn["daten"];
    if (fn !== undefined) {
      r = { ...r, ...fn.call(null, id, element, r) };
    }
  }
  return r;
};

export const neu = {
  fenster: (element: string, position: number, seite: number, sprache: string): AjaxAntwort<ANTWORTEN["Website"][15]> => uiFenster.laden("Website", 15, { element: element, position: position, seite: seite, sprache: sprache }),
  speichern: (element: string, position: number, seite: number, sprache: string): Promise<void> => ajax("Website", 16, "Element erstellen", { element: element, position: position, seite: seite, sprache: sprache, ...daten("dshWebsiteElementNeu", element) })
    .then((): void => uiLaden.meldung("Website", 12)),
};

export const bearbeiten = {
  fenster: (element: string, id: number, ueberschreiben: boolean): AjaxAntwort<ANTWORTEN["Website"][17]> => uiFenster.laden("Website", 17, { element: element, id: id }, false, undefined, ueberschreiben),
  speichern: (element: string, id: number): Promise<void> => ajax("Website", 18, "Element bearbeiten", { element: element, id: id, ...daten("dshWebsiteElementBearbeiten" + element + "_" + id, element) })
    .then((): void => uiLaden.meldung("Website", 13, false, { element: element, id: id })),
};

export const loeschen = {
  fragen: (element: string, id: number): void => uiLaden.meldung("Website", 16, "Element löschen", { element: element, id: id }),
  ausfuehren: (element: string, id: number): Promise<void> => ajax("Website", 20, false, { element: element, id: id })
    .then(() => { neuladen(); uiLaden.meldung("Website", 17, false, { element: element, id: id }); }),
};

export const setzen = {
  version: {
    fragen: (element: string, id: number, version: Version): void => uiLaden.meldung("Website", 19, "Daten ändern", { element: element, id: id, version: version }),
    ausfuehren: (element: string, id: number, version: Version): Promise<void> => ajax("Website", 22, false, { element: element, id: id, version: version })
      .then(() => {
        neuladen();
        bearbeiten.fenster(element, id, true);
        uiLaden.meldung("Website", 20, false, { element: element, id: id, version: version });
      }),
  }
};