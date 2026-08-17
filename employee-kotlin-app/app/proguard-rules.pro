# Keep Gson wire models: R8 would otherwise rename fields and break parsing.
-keep class com.mcc.payroll.data.remote.** { *; }
-keepattributes Signature,*Annotation*
-dontwarn okhttp3.**
-dontwarn retrofit2.**
