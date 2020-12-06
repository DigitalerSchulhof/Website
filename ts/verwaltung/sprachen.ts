import { SortierParameter } from "module/UI/ts/elemente/tabelle";
import ajax, { AjaxAntwort, ANTWORTEN } from "ts/ajax";
import $ from "ts/eQuery";
import * as uiFenster from "module/UI/ts/elemente/fenster";
import * as uiLaden from "module/UI/ts/elemente/laden";

export const suchen = (sort: SortierParameter): AjaxAntwort<ANTWORTEN["Website"][0]> => ajax("Website", 0, false, { ...sort });

export interface SpracheDaten {
  a2: string,
  name: string,
  namestandard: string;
  alt: string;
  aktuell: string;
  neu: string;
  sehen: string;
  bearbeiten: string;
  fehler: string;
}

export const daten = (id: string): SpracheDaten => ({
  a2: $("#" + id + "A2").getWert(),
  name: $("#" + id + "Name").getWert(),
  namestandard: $("#" + id + "NameStandard").getWert(),
  alt: $("#" + id + "Alt").getWert(),
  aktuell: $("#" + id + "Aktuell").getWert(),
  neu: $("#" + id + "Neu").getWert(),
  sehen: $("#" + id + "Sehen").getWert(),
  bearbeiten: $("#" + id + "Bearbeiten").getWert(),
  fehler: $("#" + id + "Fehler").getWert(),
});
export const neu = {
  fenster: (): AjaxAntwort<ANTWORTEN["Website"][1]> => uiFenster.laden("Website", 1),
  speichern: (): AjaxAntwort<ANTWORTEN["Website"][2]> => ajax("Website", 2, "Sprache anlegen", { ...daten("dshVerwaltungSpracheNeu") }, 0, "dshVerwaltungSprachen"),
};
export const bearbeiten = {
  fenster: (id: number): AjaxAntwort<ANTWORTEN["Website"][3]> => uiFenster.laden("Website", 3, { id: id }),
  speichern: (id: number): Promise<void> => ajax("Website", 4, "Sprache bearbeiten", { id: id, ...daten("dshVerwaltungSpracheBearbeiten" + id) }, false, "dshVerwaltungSprachen").then(() => uiLaden.meldung("Website", 1, false, { id: id })),
};
export const loeschen = {
  fragen: (id: number): void => uiLaden.meldung("Website", 2, "Sprache löschen", { id: id }),
  ausfuehren: (id: number): AjaxAntwort<ANTWORTEN["Website"][5]> => ajax("Website", 5, false, { id: id }, 3, "dshVerwaltungSprachen"),
};
export const standardsprache = {
  fragen: (id: number): void => uiLaden.meldung("Website", 4, "Standardsprache festlegen", { id: id }),
  ausfuehren: (id: number): AjaxAntwort<ANTWORTEN["Website"][6]> => ajax("Website", 6, false, { id: id }, 5, "dshVerwaltungSprachen"),
};
