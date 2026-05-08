package com.empresa.liberacioncanales;

import android.Manifest;
import android.content.pm.PackageManager;
import android.os.Build;
import android.os.Bundle;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.core.content.ContextCompat;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {

    private ActivityResultLauncher<String> solicitarNotificacionesLauncher;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        registrarSolicitudPermisoNotificaciones();
        super.onCreate(savedInstanceState);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            solicitarPermisoNotificacionesSiFalta();
        }
    }

    private void registrarSolicitudPermisoNotificaciones() {
        solicitarNotificacionesLauncher = registerForActivityResult(
                new ActivityResultContracts.RequestPermission(),
                granted -> {
                    /* El usuario aceptó o rechazó; la web puede usar Notification.requestPermission() después */
                }
        );
    }

    private void solicitarPermisoNotificacionesSiFalta() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS)
                == PackageManager.PERMISSION_GRANTED) {
            return;
        }
        solicitarNotificacionesLauncher.launch(Manifest.permission.POST_NOTIFICATIONS);
    }
}
