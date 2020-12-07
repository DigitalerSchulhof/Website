import * as seite from "./seite";
import * as elemente from "./elemente";
import elementListe from "./elemente/_export";
import verwaltung from "./verwaltung/_export";
import { SortierParameter } from "module/UI/ts/elemente/tabelle";
import { AnfrageDatenLeer } from "ts/ajax";
import { FehlermeldungenDaten } from "./verwaltung/fehlermeldungen";
import { SeiteDaten } from "./verwaltung/seiten";
import { ElementDaten } from "./elemente";
import { SpracheDaten } from "./verwaltung/sprachen";

export type Status = "a" | "i";
export type Version = "a" | "n";

export interface Daten {
  0: SortierParameter,
  1: AnfrageDatenLeer,
  2: SpracheDaten,
  3: {
    id: number;
  },
  4: {
    id: number,
  } & SpracheDaten,
  5: {
    id: number
  },
  6: {
    id: number
  },
  7: SortierParameter & {
    sprache: string
  },
  8: {
    id: number | null
  },
  9: {
    id: number | null;
  } & SeiteDaten,
  10: {
    id: number
  },
  11: {
    id: number;
  } & SeiteDaten,
  12: {
    id: number
  },
  13: {
    id: number;
  },
  14: {
    id: number,
    status: Status;
  },
  15: {
    element: string;
    seite: number;
    position: number;
    sprache: string;
  },
  16: {
    element: string;
    seite: number;
    position: number;
    sprache: string;
    status: Status;
  },
  17: {
    element: string;
    id: number
  },
  18: {
    element: string;
    id: number;
  } & ElementDaten,
  19: {
    id: number;
    sprache: string;
    version: Version;
  },
  20: {
    element: string;
    id: number
  },
  21: {
    sprache: string;
  } & FehlermeldungenDaten,
  22: {
    element: string;
    id: number
    version: Version;
  }
}



export default {
  seite: seite,
  elemente: elemente,
  element: elementListe,
  verwaltung: verwaltung,
};