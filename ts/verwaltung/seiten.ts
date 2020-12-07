import $ from "ts/eQuery";
import { SortierParameter } from "module/UI/ts/elemente/tabelle";
import ajax, { AjaxAntwort, ANTWORTEN } from "ts/ajax";
import { Status, Version } from "../_export";
import * as uiFenster from "module/UI/ts/elemente/fenster";
import * as uiLaden from "module/UI/ts/elemente/laden";
import { neuladen } from "ts/laden";

export const suchen = (sort: SortierParameter): AjaxAntwort<ANTWORTEN["Website"][7]> => {
  const sprache = $("#dshVerwaltungSeitenSprachwahl").getWert();
  return ajax("Website", 7, false, { sprache: sprache, ...sort });
};

export type Art = "i" | "m";

export interface SeiteDaten {
  art: Art,
  status: Status;
  sprachen: {
    [key: string]: {
      bezeichnung: string;
      pfad: string;
    }
  }
}

export const daten = (id: string): SeiteDaten => {
  const r: SeiteDaten = {
    art: $("#" + id + "Art").getWert() as Art,
    status: $("#" + id + "Status").getWert() as Status,
    sprachen: {}
  };
  const sprachen = $("#" + id + "Sprachen").getWert().split(";");
  for (const s of sprachen) {
    r.sprachen[s] = {
      bezeichnung: $("#" + id + "Bezeichnung" + s).getWert(),
      pfad: $("#" + id + "Pfad" + s).getWert(),
    };
  }
  return r;
};

export const neu = {
  // »id« ist hier die übergeordnete Seite um den Bezug nicht zu verlieren
  fenster: (id: number): AjaxAntwort<ANTWORTEN["Website"][8]> => uiFenster.laden("Website", 8, { id: (id || null) }),
  speichern: (id: number): AjaxAntwort<ANTWORTEN["Website"][9]> => ajax("Website", 9, "Seite anlegen", { id: (id || null), ...daten("dshVerwaltungSeiteNeu") }, 6, "dshVerwaltungSeiten")
};
export const bearbeiten = {
  fenster: (id: number): AjaxAntwort<ANTWORTEN["Website"][10]> => uiFenster.laden("Website", 10, { id: id }),
  speichern: (id: number): Promise<void> => ajax("Website", 11, "Seite bearbeiten", { id: id, ...daten("dshVerwaltungSeiteBearbeiten" + id) }, false, "dshVerwaltungSeiten")
    .then(() => uiLaden.meldung("Website", 7, false, { id: id })),
};
export const loeschen = {
  fragen: (id: number): void => uiLaden.meldung("Website", 8, "Seite löschen", { id: id }),
  ausfuehren: (id: number): Promise<void> => ajax("Website", 12, false, { id: id }, 9, "dshVerwaltungSeiten").then(() => neuladen())
};
export const startseite = {
  fragen: (id: number): void => uiLaden.meldung("Website", 10, "Zur Startseite machen", { id: id }),
  ausfuehren: (id: number): Promise<void> => ajax("Website", 13, false, { id: id }, 11, "dshVerwaltungSeiten").then(() => neuladen())
};
export const setzen = {
  status: (id: number, status: Status): Promise<void> => ajax("Website", 14, "Status ändern", { id: id, status: status }, false, "dshVerwaltungSeiten")
    .then(() => uiLaden.aus()),
  version: {
    fragen: (id: number, version: Version, sprache: string): void => uiLaden.meldung("Website", 14, "Daten ändern", { id: id, version: version, sprache: sprache }),
    ausfuehren: (id: number, version: Version, sprache: string): Promise<void> => ajax("Website", 19, false, { id: id, version: version, sprache: sprache })
      .then(() => { neuladen(); uiLaden.meldung("Website", 15, false, { version: version }); })
  }
};