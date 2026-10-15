package com.anteraja.pharma;

import static org.junit.Assert.assertEquals;
import android.content.Context;
import androidx.test.ext.junit.runners.AndroidJUnit4;
import androidx.test.platform.app.InstrumentationRegistry;
import org.junit.Test;
import org.junit.runner.RunWith;

// teammate runs this with a connected Android device, not during npm install.
@RunWith(AndroidJUnit4.class)
public class PharmaAppTest {
    @Test
    public void applicationUsesPharmaPackage() {
        Context app = InstrumentationRegistry.getInstrumentation().getTargetContext();
        assertEquals("com.anteraja.pharma", app.getPackageName());
    }
}
