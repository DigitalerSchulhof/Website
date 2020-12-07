import $ from "ts/eQuery";

interface KontaktformularDaten {
  empfaenger: {
    name: string;
    mail: string;
    beschreibung: string;
  }[];
}
export const daten = (id: number): KontaktformularDaten => {
  const empf: KontaktformularDaten["empfaenger"] = [];
  // Benachbarte Tabellen finden
  $("#" + id + "EmpfaengerNeu").parent(".dshUiTabelleO").siblings(".dshUiTabelleO").finde("form").each(e => {
    const i = parseInt(e.id.replace(`${id}Empfaenger`, ""));
    empf.push({
      name: $(e).finde(`#${id}Empfaenger${i}Name`).getWert(),
      mail: $(e).finde(`#${id}Empfaenger${i}Mail`).getWert(),
      beschreibung: $(e).finde(`#${id}Empfaenger${i}Beschreibung`).getWert(),
    });
  });
  return { empfaenger: empf };
};

export const empfaenger = {
  neu: (pre: string): void => {
    const temp = $("#" + pre + "EmpfaengerNeu").parent(".dshUiTabelleO");
    const c = temp[0].cloneNode(true) as HTMLElement;
    $(c).finde("form").einblenden();
    let outer = c.outerHTML;
    const max = parseInt(((temp[0].previousSibling || { id: pre + "Empfaenger-1" }) as { id: string }).id.replace(`${pre}Empfaenger`, ""));
    outer = outer.replace(new RegExp(`${pre}EmpfaengerNeu`, "g"), `${pre}Empfaenger${max + 1}`);
    temp[0].outerHTML = outer + temp[0].outerHTML;
  },
  loeschen: (dis: HTMLElement): void => {
    $(dis).parent("form").entfernen();
  }
};
