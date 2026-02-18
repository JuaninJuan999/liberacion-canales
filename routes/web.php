// Perfil de Usuario
Route::get('/profile', [UsuarioController::class, 'profile'])->name('profile');
Route::get('/profile/edit', [UsuarioController::class, 'editProfile'])->name('profile.edit');
Route::put('/profile', [UsuarioController::class, 'updateProfile'])->name('profile.update');