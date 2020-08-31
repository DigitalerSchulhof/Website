website.seite = {
  aendern: {
    sprache: _ => {
      let seite = $("#dshWebsiteSprache").getWert();
      core.seiteLaden(seite);
      let spracheFokus = _ => {
        $("#dshWebsiteSprache")[0].focus();
        window.removeEventListener("dshSeiteGeladen", spracheFokus);
      };
      window.addEventListener("dshSeiteGeladen", spracheFokus);
    }
  }
};