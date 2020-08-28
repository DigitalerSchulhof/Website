website.seite = {
  aendern: {
    sprache: () => {
      let seite = $("#dshWebsiteSprache").getWert();
      core.seiteLaden(seite);
      let spracheFokus = () => {
        $("#dshWebsiteSprache")[0].focus();
        window.removeEventListener("dshSeiteGeladen", spracheFokus);
      };
      window.addEventListener("dshSeiteGeladen", spracheFokus);
    }
  }
};