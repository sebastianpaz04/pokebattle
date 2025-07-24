const nombreAId = {
  charizard: 6,
  blastoise: 9,
  arcanine: 59,
  golem: 76,
  pidgeot: 18,
  sandslash: 28,
  rapidash: 78,
  poliwrath: 62,
  kabutops: 141,
  dugtrio: 51,
  tauros: 128,
  aerodactyl: 142,
  kingler: 99,
  ninetales: 38,
  marowak: 105,
  dodrio: 85,
  omastar: 139,
  rhydon: 112,
  farfetchd: 83,
  lapras: 131,
  flareon: 136,
  kabuto: 140,
  persian: 53,
  fearow: 22,
  onix: 95,
  venusaur: 3,
  victreebel: 71,
  vileplume: 45,
  tangela: 114,
  exeggutor: 103,
};

export function agregarImagenes(cartas) {
  return cartas.map(carta => {
    const idPoke = nombreAId[carta.nombre.toLowerCase()];
    return {
      ...carta,
      imagen: idPoke
        ? `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/${idPoke}.png`
        : null,
    };
  });
}

