import $ from "ts/eQuery";
import seiteLaden from "ts/laden";

export const aendern = {
  sprache: (): void => {
    const seite = $("#dshWebsiteSprache").getWert();
    seiteLaden(seite);
    const spracheFokus = () => {
      if ($("#dshWebsiteSprache").existiert()) {
        $("#dshWebsiteSprache")[0].focus();
      }
      window.removeEventListener("dshSeiteGeladen", spracheFokus);
    };
    window.addEventListener("dshSeiteGeladen", spracheFokus);
  }
};