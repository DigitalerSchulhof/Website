website.element.kontaktformulare = {
  daten: id => {
    let empf = [];
    // Benachbarte Tabellen finden
    $("#"+id+"EmpfaengerNeu").parentSelector(".dshUiTabelleO").siblingsSelector(".dshUiTabelleO").finde("form").each(e => {
        let i = parseInt(e.id.replace(`${id}Empfaenger`, ""));
        empf.push({
          name: $(e).finde(`#${id}Empfaenger${i}Name`).getWert(),
          mail: $(e).finde(`#${id}Empfaenger${i}Mail`).getWert(),
          beschreibung: $(e).finde(`#${id}Empfaenger${i}Beschreibung`).getWert(),
        });
    });
    return { empfaenger: empf };
  },
  empfaenger: {
    neu: (pre) => {
      let temp = $("#" + pre + "EmpfaengerNeu").parentSelector(".dshUiTabelleO");
      let c = temp[0].cloneNode(true);
      $(c).finde("form").einblenden();
      c = c.outerHTML;
      let max = parseInt((temp[0].previousSibling || { id: pre+"Empfaenger-1" }).id.replace(`${pre}Empfaenger`, ""));
      c = c.replace(new RegExp(`${pre}EmpfaengerNeu`, "g"), `${pre}Empfaenger${max + 1}`);
      temp[0].outerHTML = c + temp[0].outerHTML;
    },
    loeschen: (dis) => {
      $(dis).parentSelector("form").entfernen();
    },
  },
};
