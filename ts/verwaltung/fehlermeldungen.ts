import ajax from "ts/ajax";
import $ from "ts/eQuery";
import seiteLaden, { neuladen } from "ts/laden";

export interface FehlermeldungenDaten {
  titel403: string;
  inhalt403: string;
  titel404: string;
  inhalt404: string;
}

export const daten = (id: string): FehlermeldungenDaten => ({
  titel403: $("#" + id + "Titel403").getWert(),
  inhalt403: $("#" + id + "Inhalt403").getWert(),
  titel404: $("#" + id + "Titel404").getWert(),
  inhalt404: $("#" + id + "Inhalt404").getWert(),
});

export const speichern = (sprache: string): Promise<void> => ajax("Website", 21, "Änderungen speichern", { sprache: sprache, ...daten("dshVerwaltungFehlermeldung") }, 18).then(() => neuladen());

export const sprache = (): void => {
  seiteLaden($("#dshVerwaltungFehlermeldungenSprachwahl").getWert());
  const spracheFokus = () => {
    $("#dshVerwaltungFehlermeldungenSprachwahl")[0].focus();
    window.removeEventListener("dshSeiteGeladen", spracheFokus);
  };
  window.addEventListener("dshSeiteGeladen", spracheFokus);
};