export function validarNombre(nombre) {
  if (!/^[a-zA-Z0-9]+$/.test(nombre)) return 'El nombre solo puede contener caracteres alfanuméricos.';
  return null;
}

export function validarContraseña(contraseña) {
  if (contraseña.length < 8) return 'La contraseña debe tener al menos 8 caracteres.';
  if (!/[a-z]/.test(contraseña)) return 'La contraseña debe contener al menos una letra minúscula.';
  if (!/[A-Z]/.test(contraseña)) return 'La contraseña debe contener al menos una letra mayúscula.';
  if (!/[0-9]/.test(contraseña)) return 'La contraseña debe contener al menos un número.';
  if (!/[^a-zA-Z0-9]/.test(contraseña)) return 'La contraseña debe contener al menos un carácter especial.';
  return null;
}
